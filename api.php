<?php
/**
 * Unified JSON router for mass-cc-studio.
 *
 * Public actions: meta, session, login, logout.
 * All other actions require a valid signed session cookie.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mass_validator.php';
require_once __DIR__ . '/generator.php';

$method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
$action = trim((string) ($_GET['action'] ?? ''));
$body = studio_read_body();
if ($action === '') {
    $action = trim((string) ($body['action'] ?? ''));
}

function studio_bool($value, bool $default = false): bool
{
    if ($value === null || $value === '') {
        return $default;
    }
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function studio_validate_generated(array $card): array
{
    $number = (string) ($card['number'] ?? '');
    $expiry = (string) ($card['expiry'] ?? '');
    $cvv = (string) ($card['cvv'] ?? '');

    $month = '';
    $year = '';
    if ($expiry !== '') {
        $parts = explode('/', $expiry);
        $month = $parts[0] ?? '';
        $year = $parts[1] ?? '';
    }
    $fullYear = $year !== '' ? (string) convertToFullYear($year) : '';

    if ($month !== '' && $fullYear !== '' && $cvv !== '') {
        $check = massCheckCardData($number . '|' . $month . '|' . $fullYear . '|' . $cvv);
        return [
            'status' => (string) ($check['status'] ?? 'die'),
            'network' => (string) ($check['network'] ?? ''),
            'score' => (int) ($check['score'] ?? 0),
            'reason' => (string) ($check['message'] ?? ''),
            'error' => (int) ($check['error'] ?? 4),
            'card' => (string) ($check['card'] ?? $number),
        ];
    }

    $validation = massValidateGenerated(
        $number,
        $month !== '' ? $month : null,
        $fullYear !== '' ? $fullYear : null,
        $cvv !== '' ? $cvv : null
    );

    $cardLine = $number;
    if ($month !== '' || $fullYear !== '' || $cvv !== '') {
        $cardLine = $number . '|' . $month . '|' . $fullYear . '|' . $cvv;
    }

    $errorCode = $validation['status'] === 'live' ? 1 : ($validation['status'] === 'die' ? 2 : 3);

    return [
        'status' => (string) $validation['status'],
        'network' => (string) $validation['network'],
        'score' => (int) $validation['score'],
        'reason' => (string) $validation['reason'],
        'error' => $errorCode,
        'card' => $cardLine,
    ];
}

if ($method === 'GET' && $action === 'meta') {
    studio_json(200, [
        'name' => APP_TITLE,
        'tagline' => APP_TAGLINE,
        'features' => APP_FEATURES,
    ]);
}

if ($method === 'GET' && $action === 'session') {
    studio_json(200, ['authenticated' => studio_current_session() !== null]);
}

if ($method === 'POST' && $action === 'login') {
    $ip = studio_client_ip();
    $blockedFor = studio_login_blocked($ip);
    if ($blockedFor > 0) {
        studio_json(429, ['error' => 'Too many attempts. Retry in ' . $blockedFor . 's']);
    }
    if (!studio_rate_allow('login:' . $ip, LOGIN_RATE_LIMIT, LOGIN_RATE_WINDOW_SECONDS)) {
        studio_json(429, ['error' => 'Too many login attempts']);
    }

    $username = (string) ($body['username'] ?? '');
    $password = (string) ($body['password'] ?? '');
    $auth = studio_load_auth();

    if (hash_equals($auth['username'], $username) && studio_verify_password($password, $auth)) {
        studio_record_login_success($ip);
        $token = studio_issue_token($auth['username']);
        studio_set_session_cookie($token, SESSION_TTL);
        studio_json(200, ['ok' => true]);
    }

    studio_record_login_failure($ip);
    studio_json(401, ['error' => 'Invalid credentials']);
}

if ($method === 'POST' && $action === 'logout') {
    studio_set_session_cookie('', 0);
    studio_json(200, ['ok' => true]);
}

// Everything below this point requires authentication.
$session = studio_require_auth();
$ip = studio_client_ip();
if (!studio_rate_allow('api:' . $ip, API_RATE_LIMIT, 60)) {
    studio_json(429, ['error' => 'Rate limit exceeded. Try again in a minute.']);
}

if ($method === 'GET' && $action === 'settings') {
    $auth = studio_load_auth();
    studio_json(200, ['username' => $auth['username']]);
}

if ($method === 'POST' && $action === 'settings') {
    $auth = studio_load_auth();
    $currentPassword = (string) ($body['currentPassword'] ?? '');
    if (!studio_verify_password($currentPassword, $auth)) {
        studio_json(401, ['error' => 'Current password is incorrect']);
    }

    $username = trim((string) ($body['username'] ?? ''));
    if ($username === '') {
        $username = $auth['username'];
    }
    if (!preg_match('/^[\w.@-]{1,64}$/', $username)) {
        studio_json(400, ['error' => 'Username must be 1-64 characters (letters, numbers, ., _, @, -)']);
    }

    $passwordHash = $auth['passwordHash'];
    $salt = $auth['salt'];
    $newPassword = (string) ($body['newPassword'] ?? '');
    if ($newPassword !== '') {
        if (strlen($newPassword) < 8 || strlen($newPassword) > 128) {
            studio_json(400, ['error' => 'New password must be 8-128 characters']);
        }
        $salt = studio_random_salt();
        $passwordHash = studio_password_hash($newPassword, $salt);
    }

    if (!studio_save_auth([
        'username' => $username,
        'passwordHash' => $passwordHash,
        'salt' => $salt,
    ])) {
        studio_json(500, ['error' => 'Could not save settings: data directory is not writable']);
    }
    studio_json(200, ['ok' => true, 'username' => $username]);
}

if ($method === 'POST' && $action === 'generate') {
    $count = max(1, min(100000, (int) ($body['count'] ?? 1)));
    $includeExpiry = ($body['includeExpiry'] ?? true) !== false;
    $includeCvv = ($body['includeCvv'] ?? true) !== false;
    $maskPan = studio_bool($body['maskPan'] ?? false, false);
    $doValidate = studio_bool($body['validate'] ?? false, false);
    $format = in_array($body['format'] ?? 'text', ['text', 'csv', 'json', 'pipe'], true)
        ? (string) $body['format']
        : 'text';

    $brandValue = (string) ($body['brand'] ?? '');
    $brand = gen_clean_digits($brandValue) !== '' ? null : ($brandValue !== '' ? $brandValue : null);
    $bin = (string) ($body['bin'] ?? '');

    $expiryYearRaw = gen_clean_digits((string) ($body['expiryYear'] ?? ''));
    if (preg_match('/^\d{4}$/', $expiryYearRaw)) {
        $expiryYear = $expiryYearRaw;
    } elseif (preg_match('/^\d{2}$/', $expiryYearRaw)) {
        $expiryYear = '20' . $expiryYearRaw;
    } else {
        $expiryYear = null;
    }

    $cards = [];
    for ($i = 0; $i < $count; $i++) {
        $card = gen_generate_one($brand, $bin, $includeExpiry, $includeCvv, $expiryYear);

        if ($doValidate) {
            $card['validation'] = studio_validate_generated($card);
        }

        $cards[] = $card;
    }

    if ($format === 'json') {
        $jsonCards = $cards;
        if ($maskPan) {
            foreach ($jsonCards as &$jsonCard) {
                $jsonCard['number'] = $jsonCard['masked'];
                unset($jsonCard['masked']);
            }
            unset($jsonCard);
        }
        $text = json_encode($jsonCards, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } elseif ($format === 'csv') {
        $lines = ['number,expiry,cvv,brand'];
        foreach ($cards as $card) {
            $lines[] = gen_format_card($card, 'csv', $maskPan);
        }
        $text = implode("\n", $lines);
    } else {
        $lines = [];
        foreach ($cards as $card) {
            $lines[] = gen_format_card($card, $format, $maskPan);
        }
        $text = implode("\n", $lines);
    }

    studio_json(200, [
        'count' => $count,
        'generated' => count($cards),
        'validated' => count($cards),
        'cards' => $cards,
        'text' => $text,
        'format' => $format,
        'maskPan' => $maskPan,
        'validate' => $doValidate,
    ]);
}

if ($method === 'POST' && $action === 'validate') {
    studio_json(200, gen_validate_card(
        $body['number'] ?? '',
        $body['expiryMonth'] ?? null,
        $body['expiryYear'] ?? null,
        $body['cvv'] ?? null
    ));
}

if ($method === 'POST' && $action === 'bulk') {
    $maskPan = ($body['maskPan'] ?? true) !== false;
    $numbers = is_array($body['numbers'] ?? null) ? array_slice($body['numbers'], 0, 100000) : [];
    $results = [];
    foreach ($numbers as $number) {
        $line = (string) $number;
        $digits = gen_extract_card_number($line);
        if ($digits === '') {
            continue;
        }
        $validation = gen_validate_card($digits, null, null, null);
        if (!$validation['number']['valid']) {
            continue;
        }
        $results[] = [
            'number' => $digits,
            'line' => gen_format_bulk_line($line, $maskPan),
        ];
    }
    studio_json(200, ['results' => $results]);
}

if ($method === 'POST' && $action === 'bin') {
    $result = gen_bin_lookup($body['bin'] ?? '');
    if (isset($result['error'])) {
        studio_json(400, ['error' => $result['error']]);
    }
    studio_json(200, $result);
}

if ($method === 'POST' && $action === 'check') {
    $data = (string) ($body['data'] ?? '');
    studio_json(200, massCheckCardData($data));
}

if ($method === 'POST' && $action === 'check_bulk') {
    $lines = is_array($body['lines'] ?? null) ? array_slice($body['lines'], 0, 100000) : [];
    $results = [];
    foreach ($lines as $line) {
        $results[] = massCheckCardData((string) $line);
    }
    studio_json(200, ['results' => $results]);
}

studio_json(404, ['error' => 'Not found']);
