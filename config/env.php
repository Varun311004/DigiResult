<?php

declare(strict_types=1);

/**
 * Load a simple KEY=VALUE environment file without Composer dependencies.
 *
 * DigiResult uses the .env file as its explicit deployment configuration.
 * Values loaded from .env intentionally override any same-named environment
 * variables already present in the hosting process so that hosting defaults
 * (for example localhost/root) cannot silently override the application's
 * configured production database and mail settings.
 */
function dr_load_env_file(string $path): void
{
    global $DR_ENV_VALUES;

    if (!isset($DR_ENV_VALUES) || !is_array($DR_ENV_VALUES)) {
        $DR_ENV_VALUES = [];
    }

    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        // Remove a UTF-8 BOM if the file was saved with one.
        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line) ?? $line;
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = substr($line, 7);
        }

        $separator = strpos($line, '=');

        if ($separator === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separator));
        $value = trim(substr($line, $separator + 1));

        if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            continue;
        }

        // Strip matching single or double quotes around the value.
        if (
            strlen($value) >= 2
            && (
                ($value[0] === '"' && $value[strlen($value) - 1] === '"')
                || ($value[0] === "'" && $value[strlen($value) - 1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        // Store .env values in application-owned memory. This is the source of
        // truth and avoids FastCGI/SAPI environment precedence surprises.
        $DR_ENV_VALUES[$key] = $value;

        // Keep legacy/process environment consumers working when the host
        // permits putenv(), but never depend on it for application config.
        if (function_exists('putenv')) {
            @putenv($key . '=' . $value);
        }

        if (isset($_ENV) && is_array($_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Read a configuration value from the explicit .env store first.
 */
function dr_env(string $key, mixed $default = null): mixed
{
    global $DR_ENV_VALUES;

    if (
        isset($DR_ENV_VALUES)
        && is_array($DR_ENV_VALUES)
        && array_key_exists($key, $DR_ENV_VALUES)
    ) {
        return $DR_ENV_VALUES[$key];
    }

    // Fallback to local environment variables for compatibility. The second
    // argument matters on FastCGI, where SAPI-provided variables can otherwise
    // shadow values set by putenv().
    if (function_exists('getenv')) {
        $localValue = getenv($key, true);

        if ($localValue !== false) {
            return $localValue;
        }
    }

    if (isset($_ENV) && is_array($_ENV) && array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    return $default;
}
