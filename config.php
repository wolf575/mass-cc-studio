<?php
/**
 * mass-cc-studio shared configuration.
 *
 * Environment overrides are read once here so both the authentication layer
 * and the MASS validation engine use the same values.
 */

declare(strict_types=1);

define('APP_NAME', 'mass-cc-studio');
define('APP_TITLE', 'Mass CC Studio');
define('APP_TAGLINE', 'MASS 检测器 + 合成卡生成 / 校验 / BIN 查询');
define('APP_FEATURES', ['checker', 'generate', 'validate', 'bin', 'settings']);

define('DATA_DIR', __DIR__ . '/data');
define('AUTH_FILE', getenv('AUTH_FILE') ?: DATA_DIR . '/auth.json');
define('AUTH_USERNAME', getenv('AUTH_USERNAME') ?: 'admin');
define('AUTH_PASSWORD', getenv('AUTH_PASSWORD') ?: 'ChangeMe_2026!');
define('SESSION_SECRET', getenv('SESSION_SECRET') ?: 'change-this-session-secret');
define('COOKIE_SECURE', getenv('COOKIE_SECURE') === '1');
define('SESSION_TTL', 60 * 60 * 8);
define('MAX_BODY_BYTES', 16 * 1024 * 1024);

// API rate limits (requests per minute per client IP).
define('API_RATE_LIMIT', 120);
define('LOGIN_RATE_LIMIT', 10);
define('LOGIN_RATE_WINDOW_SECONDS', 300);
define('LOGIN_LOCK_AFTER_FAILURES', 5);
define('LOGIN_LOCK_SECONDS', 15 * 60);

// Card validation settings.
define('ENABLE_LUHN_CHECK', true);
define('MIN_CARD_LENGTH', 13);
define('MAX_CARD_LENGTH', 19);
define('MIN_VALID_YEAR', (int) date('Y'));
define('MAX_VALID_YEARS_AHEAD', 100);
