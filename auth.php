<?php
/**
 * Authentication, session and per-IP rate limiting for mass-cc-studio.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function studio_ensure_data_dir(): void
{
    if (!is_dir(DATA_DIR) && !@mkdir(DATA_DIR, 0770, true) && !is_dir(DATA_DIR)) {
        studio_json(500, ['error' => 'Could not create data directory']);
    }
}

function studio_json(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'");
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function studio_client_ip(): string
{
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        return (string) $_SERVER['REMOTE_ADDR'];
    }
    return 'unknown';
}

function studio_read_body(): array
{
    $length = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    if ($length > MAX_BODY_BYTES) {
        studio_json(413, ['error' => 'Payload too large']);
    }

    $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
    if (stripos($contentType, 'application/json') !== false) {
        $raw = (string) file_get_contents('php://input');
        if ($raw === '') {
            return [];
        }
        if (strlen($raw) > MAX_BODY_BYTES) {
            studio_json(413, ['error' => 'Payload too large']);
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            studio_json(400, ['error' => 'Invalid JSON']);
        }
        return $decoded;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        return $_POST;
    }

    return [];
}

function studio_read_state(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $raw = (string) file_get_contents($file);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function studio_write_state(string $file, array $data): void
{
    studio_ensure_data_dir();
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
}

function studio_rate_allow(string $key, int $limit, int $windowSeconds): bool
{
    $file = DATA_DIR . '/rate_' . md5($key) . '.json';
    $state = studio_read_state($file);
    $now = time();

    if (empty($state) || $now >= (int) ($state['reset'] ?? 0)) {
        $state = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    if ((int) ($state['count'] ?? 0) >= $limit) {
        studio_write_state($file, $state);
        return false;
    }

    $state['count'] = (int) ($state['count'] ?? 0) + 1;
    studio_write_state($file, $state);
    return true;
}

function studio_login_blocked(string $ip): int
{
    $file = DATA_DIR . '/login_' . md5($ip) . '.json';
    $state = studio_read_state($file);
    $until = (int) ($state['until'] ?? 0);
    if ($until > time()) {
        return $until - time();
    }
    return 0;
}

function studio_record_login_failure(string $ip): void
{
    $file = DATA_DIR . '/login_' . md5($ip) . '.json';
    $state = studio_read_state($file);
    if (empty($state) || $state['until'] < time()) {
        $state = ['count' => 0, 'first' => time(), 'until' => 0];
    }
    $state['count'] = (int) ($state['count'] ?? 0) + 1;
    if ($state['count'] >= LOGIN_LOCK_AFTER_FAILURES) {
        $state['until'] = time() + LOGIN_LOCK_SECONDS;
        $state['count'] = 0;
    }
    studio_write_state($file, $state);
}

function studio_record_login_success(string $ip): void
{
    @unlink(DATA_DIR . '/login_' . md5($ip) . '.json');
}

function studio_sha256_hex(string $value): string
{
    return hash('sha256', $value);
}

function studio_password_hash(string $password, string $salt): string
{
    return studio_sha256_hex($password . ':' . $salt);
}

function studio_random_salt(): string
{
    return bin2hex(random_bytes(16));
}

function studio_load_auth(): array
{
    $auth = studio_read_state(AUTH_FILE);
    if (
        isset($auth['username'], $auth['passwordHash'], $auth['salt'])
        && is_string($auth['username'])
        && is_string($auth['passwordHash'])
        && is_string($auth['salt'])
        && $auth['username'] !== ''
    ) {
        return $auth;
    }

    $salt = 'env:' . SESSION_SECRET;
    return [
        'username' => AUTH_USERNAME,
        'passwordHash' => studio_password_hash(AUTH_PASSWORD, $salt),
        'salt' => $salt,
    ];
}

function studio_save_auth(array $auth): void
{
    studio_write_state(AUTH_FILE, [
        'username' => $auth['username'],
        'passwordHash' => $auth['passwordHash'],
        'salt' => $auth['salt'],
    ]);
}

function studio_verify_password(string $password, array $auth): bool
{
    return hash_equals(
        $auth['passwordHash'],
        studio_password_hash($password, $auth['salt'])
    );
}

function studio_b64url_encode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function studio_b64url_decode(string $value): string
{
    $pad = strlen($value) % 4;
    if ($pad > 0) {
        $value .= str_repeat('=', 4 - $pad);
    }
    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    return $decoded === false ? '' : $decoded;
}

function studio_sign(string $payload): string
{
    return studio_b64url_encode(hash_hmac('sha256', $payload, SESSION_SECRET, true));
}

function studio_issue_token(string $user): string
{
    $now = time();
    $payload = studio_b64url_encode(json_encode([
        'sub' => $user,
        'iat' => $now,
        'exp' => $now + SESSION_TTL,
        'jti' => bin2hex(random_bytes(8)),
    ], JSON_UNESCAPED_SLASHES));
    return $payload . '.' . studio_sign($payload);
}

function studio_verify_token(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        return null;
    }

    if (!hash_equals(studio_sign($parts[0]), $parts[1])) {
        return null;
    }

    $raw = studio_b64url_decode($parts[0]);
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['sub'], $data['exp'])) {
        return null;
    }
    if ((int) $data['exp'] < time()) {
        return null;
    }
    return $data;
}

function studio_set_session_cookie(string $value, int $maxAge): void
{
    setcookie('session', $value, [
        'expires' => $maxAge > 0 ? time() + $maxAge : time() - 3600,
        'path' => '/',
        'secure' => COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

function studio_current_session(): ?array
{
    $token = (string) ($_COOKIE['session'] ?? '');
    return studio_verify_token($token);
}

function studio_require_auth(): array
{
    $session = studio_current_session();
    if ($session === null) {
        studio_json(401, ['error' => 'Not authenticated']);
    }
    return $session;
}
