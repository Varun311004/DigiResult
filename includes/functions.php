<?php

declare(strict_types=1);

function cfg(string $key, mixed $default = null): mixed
{
    $parts = explode('.', $key);
    $value = $GLOBALS['DR_CONFIG'] ?? [];

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }

        $value = $value[$part];
    }

    return $value;
}

function dr_url(string $path = ''): string
{
    $base = trim((string) cfg('app.base_path', ''));
    $base = str_replace('\\', '/', $base);

    // An explicit APP_BASE_PATH always wins. It is a URL path, not a
    // Windows filesystem path.
    $isWindowsFilesystemPath = (
        strlen($base) >= 3
        && isset($base[1], $base[2])
        && $base[1] === ':'
        && ($base[2] === '/' || $base[2] === '\\')
    );

    if ($isWindowsFilesystemPath) {
        $base = '';
    }

    // Automatically detect installations nested below Apache's document
    // root, e.g. C:/xampp/htdocs/DigiResult -> /DigiResult.
    if ($base === '') {
        $documentRoot = realpath(
            (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')
        );
        $projectRoot = realpath(__DIR__ . '/..');

        if ($documentRoot !== false && $projectRoot !== false) {
            $documentRoot = rtrim(
                str_replace('\\', '/', $documentRoot),
                '/'
            );
            $projectRoot = rtrim(
                str_replace('\\', '/', $projectRoot),
                '/'
            );

            if ($projectRoot === $documentRoot) {
                $base = '';
            } elseif (str_starts_with(
                strtolower($projectRoot),
                strtolower($documentRoot . '/')
            )) {
                $relative = substr(
                    $projectRoot,
                    strlen($documentRoot) + 1
                );

                $base = '/' . trim($relative, '/');
            }
        }
    }

    // Fallback for servers where filesystem paths are unavailable.
    // The current script URL contains the application's directory.
    if ($base === '') {
        $scriptName = str_replace(
            '\\',
            '/',
            (string) ($_SERVER['SCRIPT_NAME'] ?? '')
        );
        $scriptDirectory = str_replace('\\', '/', dirname($scriptName));

        if ($scriptDirectory !== '/' && $scriptDirectory !== '.') {
            $segments = array_values(
                array_filter(
                    explode('/', trim($scriptDirectory, '/')),
                    static fn(string $segment): bool => $segment !== ''
                )
            );

            if ($segments !== []) {
                $projectName = basename(realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
                $projectName = trim($projectName);

                if (
                    $projectName !== ''
                    && isset($segments[0])
                    && strcasecmp($segments[0], $projectName) === 0
                ) {
                    $base = '/' . $segments[0];
                }
            }
        }
    }

    $base = '/' . trim($base, '/');

    if ($base === '/') {
        $base = '';
    }

    $path = ltrim(
        str_replace('\\', '/', $path),
        '/'
    );

    return $base . ($path !== '' ? '/' . $path : '/');
}
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function redirect(string $path): never
{
    header('Location: ' . dr_url($path));
    exit;
}

function post_string(string $key, int $max = 255): string
{
    $value = trim((string) ($_POST[$key] ?? ''));

    return mb_substr($value, 0, $max);
}

function get_string(string $key, int $max = 255): string
{
    $value = trim((string) ($_GET[$key] ?? ''));

    return mb_substr($value, 0, $max);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): array
{
    $items = $_SESSION['_flash'] ?? [];

    unset($_SESSION['_flash']);

    return is_array($items) ? $items : [];
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(?string $token): bool
{
    if (!is_string($token)) {
        return false;
    }

    if (!isset($_SESSION['_csrf'])) {
        return false;
    }

    $token = trim($token);
    $sessionToken = trim((string) $_SESSION['_csrf']);

    if ($token === '' || $sessionToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

function require_csrf(): void
{
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit(
            'Invalid or expired form token. '
            . 'Please go back and try again.'
        );
    }
}

function mask_email(string $email): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'your registered email';
    }

    [$local, $domain] = explode('@', $email, 2);
    $visible = max(1, min(2, strlen($local)));

    return
        substr($local, 0, $visible)
        . str_repeat(
            '•',
            max(1, strlen($local) - $visible)
        )
        . '@'
        . $domain;
}

function normalize_subject(string $subject): string
{
    return strtolower(
        trim(
            preg_replace(
                '/\s+/',
                ' ',
                str_replace('_', ' ', $subject)
            )
        )
    );
}

function audit(
    string $action,
    ?int $adminId = null,
    ?string $details = null
): void {
    try {
        $conn = db();

        $stmt = $conn->prepare(
            'INSERT INTO audit_logs '
            . '(admin_id, action, details, ip_address, user_agent) '
            . 'VALUES (?, ?, ?, ?, ?)'
        );

        if (!$stmt) {
            return;
        }

        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = mb_substr(
            (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
            0,
            500
        );

        $stmt->bind_param(
            'issss',
            $adminId,
            $action,
            $details,
            $ip,
            $ua
        );

        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log(
            'DigiResult audit failure: '
            . $e->getMessage()
        );
    }
}

function safe_int(
    ?string $value,
    int $default = 0
): int {
    $n = filter_var(
        $value,
        FILTER_VALIDATE_INT
    );

    return $n === false
        ? $default
        : (int) $n;
}
