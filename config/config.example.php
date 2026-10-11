<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

dr_load_env_file(__DIR__ . '/../.env');

return [
    'app' => [
        'name' => (string) dr_env('APP_NAME', 'DigiResult'),
        'institute' => (string) dr_env(
            'APP_INSTITUTE',
            'New Horizon Institute of Technology and Management'
        ),
        'address' => (string) dr_env(
            'APP_ADDRESS',
            'Anand Nagar, Kavesar, Off Ghodbunder Road, Thane (W) - 400615.'
        ),
        'base_path' => (string) dr_env('APP_BASE_PATH', ''),
        'env' => (string) dr_env('APP_ENV', 'production'),
        'dev_otp_mode' => filter_var(
            dr_env('APP_DEV_OTP_MODE', 'false'),
            FILTER_VALIDATE_BOOLEAN
        ),
    ],

    'db' => [
        'host' => (string) dr_env('DB_HOST', '127.0.0.1'),
        'port' => (int) dr_env('DB_PORT', 3306),
        'name' => (string) dr_env('DB_NAME', 'srms'),
        'user' => (string) dr_env('DB_USER', 'root'),
        'pass' => (string) dr_env('DB_PASSWORD', ''),
    ],

    'mail' => [
        'mode' => (string) dr_env('MAIL_MODE', 'brevo_api'),
        'from_email' => (string) dr_env(
            'FROM_EMAIL',
            dr_env('DIGIRESULT_FROM_EMAIL', '')
        ),
        'from_name' => (string) dr_env('FROM_NAME', 'DigiResult'),
        'reply_to' => (string) dr_env('REPLY_TO', ''),
        'brevo_api_url' => (string) dr_env(
            'BREVO_API_URL',
            'https://api.brevo.com/v3/smtp/email'
        ),
        'brevo_api_key' => (string) dr_env(
            'BREVO_API_KEY',
            dr_env('DIGIRESULT_BREVO_API_KEY', '')
        ),
        'test_recipient' => (string) dr_env(
            'TEST_RECIPIENT',
            dr_env('DIGIRESULT_TEST_EMAIL', '')
        ),
        'brevo_timeout_seconds' => (int) dr_env(
            'BREVO_TIMEOUT_SECONDS',
            15
        ),
        'smtp' => [
            'host' => (string) dr_env('SMTP_HOST', ''),
            'port' => (int) dr_env('SMTP_PORT', 587),
            'encryption' => (string) dr_env('SMTP_ENCRYPTION', 'tls'),
            'username' => (string) dr_env('SMTP_USERNAME', ''),
            'password' => (string) dr_env('SMTP_PASSWORD', ''),
        ],
    ],

    'security' => [
        'otp_ttl_minutes' => (int) dr_env('OTP_TTL_MINUTES', 10),
        'otp_resend_seconds' => (int) dr_env('OTP_RESEND_SECONDS', 60),
        'otp_max_requests_15m' => (int) dr_env(
            'OTP_MAX_REQUESTS_15M',
            5
        ),
        'otp_max_attempts' => (int) dr_env('OTP_MAX_ATTEMPTS', 5),
        'admin_password_reset_ttl_minutes' => (int) dr_env(
            'ADMIN_PASSWORD_RESET_TTL_MINUTES',
            15
        ),
    ],
];
