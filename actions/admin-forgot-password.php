<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/mailer.php';


if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    redirect(
        'admin/forgot-password.php'
    );
}


require_csrf();


$email = strtolower(
    post_string(
        'email',
        255
    )
);


$genericMessage =
    'If an administrator account uses that email address, '
    . 'a password reset link has been sent.';


/*
|--------------------------------------------------------------------------
| VALIDATE EMAIL
|--------------------------------------------------------------------------
*/

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    flash(
        'success',
        $genericMessage
    );

    redirect(
        'admin/forgot-password.php'
    );
}


/*
|--------------------------------------------------------------------------
| REQUEST RATE LIMIT
|--------------------------------------------------------------------------
*/

$maxRequests = 5;
$cooldownSeconds = 60;


$stmt = db()->prepare(
    <<<'SQL'
SELECT COUNT(*) AS total
FROM admin_password_resets
WHERE (
    email = ?
    OR request_ip = ?
)
AND created_at >= DATE_SUB(
    NOW(),
    INTERVAL 15 MINUTE
)
SQL
);


if (!$stmt) {

    error_log(
        'DigiResult password reset rate-limit query failed: '
        . db()->error
    );

    flash(
        'error',
        'Password reset is temporarily unavailable. Please try again later.'
    );

    redirect(
        'admin/forgot-password.php'
    );
}


$ip = (string) (
    $_SERVER['REMOTE_ADDR'] ?? ''
);


$stmt->bind_param(
    'ss',
    $email,
    $ip
);


$stmt->execute();


$countRow =
    $stmt
        ->get_result()
        ->fetch_assoc()
        ?: [];


$stmt->close();


if (
    (int) (
        $countRow['total'] ?? 0
    ) >= $maxRequests
) {

    flash(
        'warning',
        'Too many password-reset requests. Please wait a few minutes before trying again.'
    );

    redirect(
        'admin/forgot-password.php'
    );
}


/*
|--------------------------------------------------------------------------
| FIND ADMIN
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare(
    <<<'SQL'
SELECT
    id,
    user_id
FROM admincreds
WHERE LOWER(user_id) = LOWER(?)
LIMIT 1
SQL
);


if (!$stmt) {

    error_log(
        'DigiResult admin lookup failed: '
        . db()->error
    );

    flash(
        'error',
        'Password reset is temporarily unavailable. Please try again later.'
    );

    redirect(
        'admin/forgot-password.php'
    );
}


$stmt->bind_param(
    's',
    $email
);


$stmt->execute();


$admin =
    $stmt
        ->get_result()
        ->fetch_assoc()
        ?: null;


$stmt->close();


if (!$admin) {

    audit(
        'admin_password_reset_requested',
        null,
        'Password reset requested for an unknown administrator email.'
    );


    flash(
        'success',
        $genericMessage
    );


    redirect(
        'admin/forgot-password.php'
    );
}


$adminId = (int) $admin['id'];


/*
|--------------------------------------------------------------------------
| SHORT RESEND COOLDOWN
|--------------------------------------------------------------------------
*/

$cooldownStmt = db()->prepare(
    <<<'SQL'
SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, created_at, NOW())) AS age_seconds
FROM admin_password_resets
WHERE admin_id = ?
ORDER BY id DESC
LIMIT 1
SQL
);


if ($cooldownStmt) {

    $cooldownStmt->bind_param(
        'i',
        $adminId
    );

    $cooldownStmt->execute();

    $cooldownRow =
        $cooldownStmt
            ->get_result()
            ->fetch_assoc()
        ?: null;

    $cooldownStmt->close();


    if ($cooldownRow) {

        $elapsed = max(
            0,
            (int) ($cooldownRow['age_seconds'] ?? 0)
        );

        $remaining = max(
            0,
            $cooldownSeconds - $elapsed
        );


        if ($remaining > 0) {

            flash(
                'warning',
                'Please wait ' . $remaining . ' seconds before requesting another reset email.'
            );

            redirect(
                'admin/forgot-password.php'
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| CREATE TOKEN
|--------------------------------------------------------------------------
*/

$token = bin2hex(
    random_bytes(32)
);


$tokenHash = hash(
    'sha256',
    $token
);


$ttlMinutes = max(
    5,
    min(
        60,
        (int) cfg(
            'security.admin_password_reset_ttl_minutes',
            15
        )
    )
);


/*
|--------------------------------------------------------------------------
| DELETE OLD TOKENS
|--------------------------------------------------------------------------
*/

$cleanup = db()->prepare(
    <<<'SQL'
DELETE FROM admin_password_resets
WHERE
    email = ?
    OR expires_at < NOW()
    OR used_at IS NOT NULL
SQL
);


if ($cleanup) {

    $cleanup->bind_param(
        's',
        $email
    );

    $cleanup->execute();

    $cleanup->close();
}


/*
|--------------------------------------------------------------------------
| STORE RESET TOKEN
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare(
    <<<'SQL'
INSERT INTO admin_password_resets (
    admin_id,
    email,
    token_hash,
    expires_at,
    request_ip
)
VALUES (
    ?,
    ?,
    ?,
    DATE_ADD(NOW(), INTERVAL ? MINUTE),
    ?
)
SQL
);


if (!$stmt) {

    error_log(
        'DigiResult password reset INSERT prepare failed: '
        . db()->error
    );

    flash(
        'error',
        'Password reset is temporarily unavailable. Please try again later.'
    );

    redirect(
        'admin/forgot-password.php'
    );
}


$stmt->bind_param(
    'issis',
    $adminId,
    $email,
    $tokenHash,
    $ttlMinutes,
    $ip
);


if (!$stmt->execute()) {

    error_log(
        'DigiResult password reset INSERT failed: '
        . $stmt->error
    );

    $stmt->close();

    flash(
        'error',
        'Password reset is temporarily unavailable. Please try again later.'
    );

    redirect(
        'admin/forgot-password.php'
    );
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| RESET URL
|--------------------------------------------------------------------------
*/

$scheme = 'http';


if (
    (
        !empty($_SERVER['HTTPS'])
        &&
        $_SERVER['HTTPS'] !== 'off'
    )
    ||
    strtolower(
        (string) (
            $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''
        )
    ) === 'https'
) {

    $scheme = 'https';
}


$host = preg_replace(
    '/[^a-zA-Z0-9\.\-:\[\]]/',
    '',
    (string) (
        $_SERVER['HTTP_HOST'] ?? ''
    )
);


if ($host === '') {

    $host = 'localhost';
}


$resetUrl =
    $scheme
    . '://'
    . $host
    . dr_url(
        'admin/reset-password.php?token='
        . rawurlencode($token)
    );


/*
|--------------------------------------------------------------------------
| SEND EMAIL
|--------------------------------------------------------------------------
*/

$sent = send_admin_password_reset_email(
    $email,
    $resetUrl,
    $ttlMinutes
);


if (!$sent) {

    $mailError = mailer_last_error();


    error_log(
        'DigiResult admin password reset email failed for '
        . $email
        . ': '
        . $mailError
    );


    $delete = db()->prepare(
        'DELETE FROM admin_password_resets
         WHERE token_hash = ?
         LIMIT 1'
    );


    if ($delete) {

        $delete->bind_param(
            's',
            $tokenHash
        );

        $delete->execute();

        $delete->close();
    }


    $safeDetail = '';


    if (
        (string) cfg(
            'app.env',
            'production'
        ) === 'local'
        &&
        $mailError !== ''
    ) {

        $safeDetail =
            ' Details: '
            . $mailError;
    }


    flash(
        'error',
        'The reset email could not be sent. Please check the email configuration.'
        . $safeDetail
    );


    redirect(
        'admin/forgot-password.php'
    );
}


/*
|--------------------------------------------------------------------------
| BREVO MESSAGE ID
|--------------------------------------------------------------------------
|
| This is extremely useful for diagnosing "email accepted by Brevo
| but not visible in Gmail" situations.
|--------------------------------------------------------------------------
*/

$messageId = mailer_last_message_id();


$auditDetails =
    'Administrator password reset email accepted by mail provider.'
    . (
        $messageId !== ''
            ? ' messageId=' . $messageId
            : ''
    );


audit(
    'admin_password_reset_requested',
    $adminId,
    $auditDetails
);


/*
|--------------------------------------------------------------------------
| SUCCESS MESSAGE
|--------------------------------------------------------------------------
*/

if ($messageId !== '') {

    flash(
        'success',
        'Reset email sent successfully. Check your inbox and spam folder. '
        // . 'Brevo message ID: '
        // . $messageId
    );

} else {

    flash(
        'success',
        $genericMessage
    );
}


redirect(
    'admin/forgot-password.php'
);