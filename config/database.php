<?php

declare(strict_types=1);

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $config = $GLOBALS['DR_CONFIG']['db'];

    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = @new mysqli(
        (string) $config['host'],
        (string) $config['user'],
        (string) $config['pass'],
        (string) $config['name'],
        (int) $config['port']
    );

    if ($conn->connect_errno) {
        error_log(
            'DigiResult database connection failed: '
            . $conn->connect_error
        );

        throw new RuntimeException(
            'Database connection is unavailable. Check config/config.php.'
        );
    }

    if (!$conn->set_charset('utf8mb4')) {
        throw new RuntimeException(
            'Database character set could not be initialized.'
        );
    }

    return $conn;
}
