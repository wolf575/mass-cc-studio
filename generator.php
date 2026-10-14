<?php
/**
 * Generator / validator logic ported from the Node.js
 * CC-Checker-Validator-Generator service. Function names are prefixed with
 * gen_ so they can coexist with the MASS engine in the same request.
 */

declare(strict_types=1);

function gen_clean_digits($value): string
{
    return preg_replace('/\D/', '', (string) ($value ?? ''));
}

function gen_luhn10(string $identifier): bool
{
    $sum = 0;
    $alt = false;
    for ($i = strlen($identifier) - 1; $i >= 0; $i--) {
        $n = (int) $identifier[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) {
                $n = ($n % 10) + 1;
            }
        }
        $alt = !$alt;
        $sum += $n;
    }
    return $sum % 10 === 0;
}

function gen_luhn_check_digit(string $payload): int
{
    $total = 0;
    $len = strlen($payload);
    for ($i = 0; $i < $len; $i++) {
        $d = (int) $payload[$len - 1 - $i];
        if ($i % 2 === 0) {
            $d *= 2;
            if ($d > 9) {
                $d -= 9;
            }
        }
        $total += $d;
    }
    return (10 - ($total % 10)) % 10;
}

const GEN_BRANDS = [
    ['key' => 'visa', 'name' => 'Visa', 'prefixes' => ['4'], 'lengths' => [16], 'cvv' => 3],
    ['key' => 'mastercard', 'name' => 'Mastercard', 'prefixes' => ['51', '52', '53', '54', '55', '2221', '2222', '2223', '2224', '2225', '2226', '2227', '2228', '2229', '223', '224', '225', '226', '227', '228', '229', '23', '24', '25', '26', '270', '271', '2720'], 'lengths' => [16], 'cvv' => 3],
    ['key' => 'amex', 'name' => 'American Express', 'prefixes' => ['34', '37'], 'lengths' => [15], 'cvv' => 4],
    ['key' => 'discover', 'name' => 'Discover', 'prefixes' => ['6011', '644', '645', '646', '647', '648', '649', '65'], 'lengths' => [16, 19], 'cvv' => 3],
    ['key' => 'diners', 'name' => 'Diners Club', 'prefixes' => ['300', '301', '302', '303', '304', '305', '36', '38', '39'], 'lengths' => [14], 'cvv' => 3],
    ['key' => 'jcb', 'name' => 'JCB', 'prefixes' => ['3528', '3529', '353', '354', '355', '356', '357', '358', '1800', '2131'], 'lengths' => [15, 16, 19], 'cvv' => 3],
    ['key' => 'unionpay', 'name' => 'UnionPay', 'prefixes' => ['62'], 'lengths' => [16, 17, 18, 19], 'cvv' => 3],
    ['key' => 'maestro', 'name' => 'Maestro', 'prefixes' => ['5018', '5020', '5038', '500', '501', '502', '503', '504', '505', '506', '507', '508', '509', '56', '57', '58', '59', '6'], 'lengths' => [12, 13, 14, 15, 16, 17, 18, 19], 'cvv' => 3],
];

function gen_detect_brand(string $digits): ?array
{
    $best = null;
    $bestLen = -1;
    foreach (GEN_BRANDS as $brand) {
        foreach ($brand['prefixes'] as $prefix) {
            if (strpos($digits, $prefix) === 0 && strlen($prefix) > $bestLen) {
                $best = $brand;
                $bestLen = strlen($prefix);
            }
        }
    }
    return $best;
}

function gen_mask(string $digits): string
{
    if (strlen($digits) < 10) {
        return $digits;
    }
    return substr($digits, 0, 6) . str_repeat('*', strlen($digits) - 10) . substr($digits, -4);
}

function gen_expiry_for_year(?string $year): string
{
    $curY = (int) date('Y');
    $curM = (int) date('n');
    $y = $year !== null && $year !== '' ? (int) $year : ($curY + 1 + random_int(0, 2));
    if ($y === $curY) {
        $month = $curM + random_int(0, 12 - $curM);
    } else {
        $month = 1 + random_int(0, 11);
    }
    $yearPart = $y >= 2100 ? (string) $y : str_pad((string) ($y % 100), 2, '0', STR_PAD_LEFT);
    return str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '/' . $yearPart;
}

function gen_standard_length(array $brand): int
{
    return in_array(16, $brand['lengths'], true) ? 16 : $brand['lengths'][0];
}

function gen_generate_one(?string $brandKey, ?string $bin, bool $includeExpiry, bool $includeCvv, ?string $expiryYear): array
{
    $prefix = '';
    $length = 16;
    $brand = null;

    if ($brandKey !== null && $brandKey !== '') {
        foreach (GEN_BRANDS as $candidate) {
            if ($candidate['key'] === $brandKey) {
                $brand = $candidate;
                break;
            }
        }
    }

    $binDigits = gen_clean_digits($bin);
    if (strlen($binDigits) >= 4) {
        $prefix = $binDigits;
        $brand = gen_detect_brand($prefix);
        $length = $brand ? gen_standard_length($brand) : 16;
        $length = min(19, max(strlen($prefix) + 1, $length));
    } else {
        if ($brand === null) {
            $brand = GEN_BRANDS[random_int(0, count(GEN_BRANDS) - 1)];
        }
        $prefix = $brand['prefixes'][random_int(0, count($brand['prefixes']) - 1)];
        $length = gen_standard_length($brand);
    }

    $body = $prefix;
    while (strlen($body) < $length - 1) {
        $body .= (string) random_int(0, 9);
    }
    $number = $body . (string) gen_luhn_check_digit($body);
    $cvvSize = $brand ? (int) $brand['cvv'] : 3;
    $cvv = null;
    if ($includeCvv) {
        $cvv = '';
        for ($i = 0; $i < $cvvSize; $i++) {
            $cvv .= (string) random_int(0, 9);
        }
    }
    $expiry = $includeExpiry ? gen_expiry_for_year($expiryYear) : null;

    return [
        'number' => $number,
        'masked' => gen_mask($number),
        'brand' => $brand ? $brand['name'] : 'Custom BIN',
        'expiry' => $expiry,
        'cvv' => $cvv,
    ];
}

function gen_format_card(array $card, string $format, bool $maskPan): string
{
    $num = $maskPan ? $card['masked'] : $card['number'];
    $expiry = (string) ($card['expiry'] ?? '');
    $cvv = (string) ($card['cvv'] ?? '');

    if ($format === 'csv') {
        return implode(',', [$num, $expiry, $cvv, $card['brand']]);
    }
    if ($format === 'pipe') {
        $parts = explode('/', $expiry === '' ? '/' : $expiry);
        $mm = $parts[0] ?? '';
        $yy = $parts[1] ?? '';
        $yyyy = $yy === '' ? '' : (strlen($yy) === 2 ? '20' . $yy : $yy);
        return implode('|', [$num, $mm, $yyyy, $cvv]);
    }
    return $num . '  ' . ($expiry ?: '    ') . '  ' . ($cvv ?: '   ') . '  ' . $card['brand'];
}

function gen_extract_card_number(string $line): string
{
    $s = trim($line);
    if ($s === '') {
        return '';
    }
    if (strpos($s, '|') !== false) {
        $part = trim(explode('|', $s)[0]);
        return gen_clean_digits($part) !== '' ? gen_clean_digits($part) : gen_clean_digits($s);
    }
    if (strpos($s, ',') !== false) {
        $part = trim(explode(',', $s)[0]);
        return gen_clean_digits($part) !== '' ? gen_clean_digits($part) : gen_clean_digits($s);
    }

    $tokens = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
    if (count($tokens) === 0) {
        return '';
    }

    $allNumeric = true;
    foreach ($tokens as $token) {
        if (!preg_match('/^[\d-]+$/', $token)) {
            $allNumeric = false;
            break;
        }
    }
    if ($allNumeric) {
        return gen_clean_digits($s);
    }

    $digits = '';
    foreach ($tokens as $token) {
        if (preg_match('/^[\d-]+$/', $token)) {
            $digits .= gen_clean_digits($token);
        } else {
            break;
        }
    }
    if (preg_match('/^\d{12,19}$/', $digits)) {
        return $digits;
    }

    $first = gen_clean_digits($tokens[0]);
    if (preg_match('/^\d{12,19}$/', $first)) {
        return $first;
    }
    return gen_clean_digits($s);
}

function gen_mask_card_number_token(string $token): string
{
    $t = trim($token);
    $digits = gen_clean_digits($t);
    if (preg_match('/^\d{12,19}$/', $digits) && preg_match('/^[\d\s-]+$/', $t)) {
        return gen_mask($digits);
    }
    return $t;
}

function gen_format_bulk_line(string $line, bool $maskPan): string
{
    $s = trim($line);
    if ($s === '' || !$maskPan) {
        return $s;
    }
    if (preg_match('/^[\d\s-]+$/', $s)) {
        return gen_mask(gen_clean_digits($s));
    }
    if (strpos($s, '|') !== false) {
        $parts = explode('|', $s);
        $parts[0] = gen_mask_card_number_token($parts[0]);
        return implode('|', $parts);
    }
    if (strpos($s, ',') !== false) {
        $parts = explode(',', $s);
        $parts[0] = gen_mask_card_number_token($parts[0]);
        return implode(',', $parts);
    }
    if (preg_match('/^([\d][\d\s-]*?)(?=\s+\S*[^\d\s-]|$)/', $s, $m)) {
        $digits = gen_clean_digits($m[1]);
        if (preg_match('/^\d{12,19}$/', $digits)) {
            return gen_mask($digits) . substr($s, strlen($m[1]));
        }
    }
    return $s;
}

function gen_validate_expiry(string $month, string $year): array
{
    $m = (int) $month;
    $y = (int) $year;
    if (!preg_match('/^\d{1,2}$/', $month) || !preg_match('/^\d{2}$|^\d{4}$/', $year)) {
        return ['provided' => true, 'valid' => false, 'message' => 'Invalid expiry format'];
    }
    if (strlen($year) === 2) {
        $y += 2000;
    }
    $curY = (int) date('Y');
    $curM = (int) date('n');
    $monthOk = $m >= 1 && $m <= 12;
    $notExpired = $y > $curY || ($y === $curY && $m >= $curM);
    $notTooFar = $y <= $curY + 150;
    $valid = $monthOk && $notExpired && $notTooFar;
    return [
        'provided' => true,
        'valid' => $valid,
        'month' => $m,
        'year' => $y,
        'message' => $valid
            ? 'Valid (' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) . '/' . $y . ')'
            : 'Invalid or expired',
    ];
}

function gen_validate_card($number, $expMonth, $expYear, $cvv): array
{
    $digits = gen_clean_digits($number);
    $brand = gen_detect_brand($digits);
    $valid = strlen($digits) >= 12 && gen_luhn10($digits);
    $maxLen = $brand ? max($brand['lengths']) : 16;
    $lengthOk = $brand ? in_array(strlen($digits), $brand['lengths'], true) : false;
    $potential = $brand ? (strlen($digits) < $maxLen || (strlen($digits) === $maxLen && $valid)) : false;

    $expiry = null;
    if ($expMonth !== null && $expMonth !== '' && $expYear !== null && $expYear !== '') {
        $expiry = gen_validate_expiry((string) $expMonth, (string) $expYear);
    }

    $cvvResult = null;
    if ($cvv !== null && (string) $cvv !== '') {
        $size = $brand ? (int) $brand['cvv'] : 3;
        $cvvDigits = gen_clean_digits($cvv);
        $cvvResult = [
            'provided' => true,
            'size' => $size,
            'valid' => strlen($cvvDigits) === $size,
            'message' => strlen($cvvDigits) === $size ? 'Valid (' . $size . ' digits)' : 'Expected ' . $size . ' digits',
        ];
    }

    return [
        'number' => [
            'input' => $digits,
            'masked' => gen_mask($digits),
            'valid' => $valid,
            'potential' => $potential,
            'lengthOk' => $lengthOk,
            'length' => strlen($digits),
            'lengths' => $brand ? $brand['lengths'] : null,
            'brand' => $brand ? ['name' => $brand['name'], 'type' => $brand['key']] : null,
        ],
        'expiry' => $expiry,
        'cvv' => $cvvResult,
    ];
}

const GEN_BIN_TABLE = [
    ['400016', 'Visa', 'Debit', 'Chase Bank', 'US'],
    ['400005', 'Visa', 'Debit', 'Wells Fargo', 'US'],
    ['411111', 'Visa', 'Credit', 'Test BIN', 'US'],
    ['424242', 'Visa', 'Credit', 'Test BIN', 'US'],
    ['515676', 'Mastercard', 'Credit', 'Citibank', 'US'],
    ['510510', 'Mastercard', 'Credit', 'Test BIN', 'US'],
    ['555555', 'Mastercard', 'Credit', 'Test BIN', 'US'],
    ['222100', 'Mastercard', 'Credit', 'Test 2-series', 'US'],
    ['340005', 'American Express', 'Credit', 'American Express', 'US'],
    ['371449', 'American Express', 'Credit', 'American Express', 'GB'],
    ['378282', 'American Express', 'Credit', 'Test BIN', 'US'],
    ['601100', 'Discover', 'Credit', 'Discover Bank', 'US'],
    ['601111', 'Discover', 'Credit', 'Test BIN', 'US'],
    ['490301', 'Visa', 'Debit', 'Revolut', 'GB'],
    ['520473', 'Mastercard', 'Prepaid', 'N26', 'DE'],
    ['621234', 'UnionPay', 'Debit', 'Bank of China', 'CN'],
    ['352800', 'JCB', 'Credit', 'JCB', 'JP'],
    ['300000', 'Diners Club', 'Credit', 'Diners Club', 'US'],
    ['675964', 'Maestro', 'Debit', 'Maestro', 'GB'],
    ['502091', 'Maestro', 'Debit', 'Maestro', 'DE'],
];

function gen_bin_lookup($raw): array
{
    $digits = gen_clean_digits($raw);
    if (strlen($digits) < 6) {
        return ['error' => 'BIN must be 6-11 digits'];
    }

    $best = null;
    $bestLen = -1;
    foreach (GEN_BIN_TABLE as $entry) {
        $bin = $entry[0];
        if (strpos($digits, $bin) === 0 && strlen($bin) > $bestLen) {
            $best = $entry;
            $bestLen = strlen($bin);
        }
    }
    if ($best !== null) {
        return [
            'bin' => $best[0],
            'brand' => $best[1],
            'type' => $best[2],
            'issuer' => $best[3],
            'country' => $best[4],
            'source' => 'builtin',
        ];
    }

    $brand = gen_detect_brand($digits);
    return [
        'bin' => substr($digits, 0, 6),
        'brand' => $brand ? $brand['name'] : 'Unknown',
        'type' => null,
        'issuer' => null,
        'country' => null,
        'source' => 'prefix-only',
    ];
}
