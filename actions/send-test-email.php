<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/mailer.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/health.php');
}

require_csrf();

$to = strtolower(
    post_string('email', 255)
);

if ($to === '') {
    $to = strtolower(
        (string) (
            $_SESSION['admin_user']
            ?? cfg(
                'mail.test_recipient',
                ''
            )
        )
    );
}

if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    flash(
        'error',
        'Enter a valid test recipient email.'
    );

    redirect('admin/health.php');
}

if (send_test_email($to)) {
    $messageId = mailer_last_message_id();

    audit(
        'email_test_sent',
        (int) $_SESSION['admin_id'],
        'Brevo test email sent to '
        . substr($to, 0, 120)
        . (
            $messageId !== ''
                ? ' messageId=' . $messageId
                : ''
        )
    );

    flash(
        'success',
        'Test email sent to ' . $to . '. Check the inbox/spam folder.'
    );
} else {
    $detail = mailer_last_error();

    audit(
        'email_test_failed',
        (int) $_SESSION['admin_id'],
        substr($detail, 0, 300)
    );

    flash(
        'error',
        'Email test failed: '
        . (
            $detail !== ''
                ? $detail
                : 'Unknown mail error.'
        )
    );
}

redirect('admin/health.php');
