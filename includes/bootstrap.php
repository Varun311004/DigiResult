<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_name('DIGIRESULTSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$configPath = __DIR__ . '/../config/config.php';

if (!is_file($configPath)) {
    http_response_code(500);
    exit(
        'DigiResult configuration is missing. '
        . 'Copy config/config.example.php to config/config.php and configure the environment.'
    );
}

$GLOBALS['DR_CONFIG'] = require $configPath;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
