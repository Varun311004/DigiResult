<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/student-queries.php';
require_once __DIR__ . '/../services/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

require_csrf();

$ien = post_string('ien', 20);
$email = strtolower(
    post_string('email', 255)
);

if (
    $ien === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    flash(
        'error',
        'Enter a valid IEN number and registered email.'
    );

    redirect('index.php');
}

$student = find_student_by_ien_email(
    $ien,
    $email
);

if (!$student) {
    flash(
        'error',
        'The IEN number and email could not be verified. Please check the details and try again.'
    );

    redirect('index.php');
}

$conn = db();

$wait = (int) cfg(
    'security.otp_resend_seconds',
    60
);

$maxRequests = (int) cfg(
    'security.otp_max_requests_15m',
    5
);

$ip = (string) (
    $_SERVER['REMOTE_ADDR'] ?? ''
);

/*
 * Throttle both the student identity and the source IP so one client
 * cannot rotate IEN/email pairs to bypass the request limit.
 */

if ($ip !== '') {
    $ipStmt = $conn->prepare(
        'SELECT COUNT(*) AS total
         FROM student_otps
         WHERE request_ip = ?
           AND created_at >= (
               NOW() - INTERVAL 15 MINUTE
           )'
    );

    $ipStmt->bind_param(
        's',
        $ip
    );

    $ipStmt->execute();

    $ipCount = (int) (
        $ipStmt
            ->get_result()
            ->fetch_assoc()['total']
        ?? 0
    );

    $ipStmt->close();

    if ($ipCount >= $maxRequests * 2) {
        flash(
            'error',
            'Too many OTP requests from this network. Please wait and try again later.'
        );

        redirect('index.php');
    }
}

$stmt = $conn->prepare(
    'SELECT GREATEST(
        0,
        TIMESTAMPDIFF(
            SECOND,
            created_at,
            NOW()
        )
     ) AS age_seconds
     FROM student_otps
     WHERE ien_number = ?
       AND email = ?
     ORDER BY id DESC
     LIMIT 1'
);

$stmt->bind_param(
    'ss',
    $ien,
    $email
);

$stmt->execute();

$last = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

if ($last) {
    /*
     * Calculate elapsed time inside MySQL so PHP and DB timezone settings
     * cannot create a false 5h30m+ cooldown on XAMPP/hosting.
     */

    $elapsed = max(
        0,
        (int) ($last['age_seconds'] ?? 0)
    );

    $remaining = max(
        0,
        min(
            $wait,
            $wait - $elapsed
        )
    );

    if ($remaining > 0) {
        flash(
            'warning',
            'Please wait '
            . $remaining
            . ' seconds before requesting another OTP.'
        );

        $_SESSION['otp_pending'] = true;
        $_SESSION['otp_pending_ien'] = $ien;
        $_SESSION['otp_pending_email'] = $email;
        $_SESSION['otp_resend_available_at'] =
            time() + $remaining;

        redirect('index.php');
    }
}

$stmt = $conn->prepare(
    'SELECT COUNT(*) AS total
     FROM student_otps
     WHERE ien_number = ?
       AND email = ?
       AND created_at >= (
           NOW() - INTERVAL 15 MINUTE
       )'
);

$stmt->bind_param(
    'ss',
    $ien,
    $email
);

$stmt->execute();

$count = (int) (
    $stmt
        ->get_result()
        ->fetch_assoc()['total']
    ?? 0
);

$stmt->close();

if ($count >= $maxRequests) {
    flash(
        'error',
        'Too many OTP requests. Please wait and try again later.'
    );

    redirect('index.php');
}

$otp = (string) random_int(
    100000,
    999999
);

$hash = password_hash(
    $otp,
    PASSWORD_DEFAULT
);

$expiryMinutes = (int) cfg(
    'security.otp_ttl_minutes',
    10
);

$stmt = $conn->prepare(
    'UPDATE student_otps
     SET consumed_at = NOW()
     WHERE ien_number = ?
       AND email = ?
       AND consumed_at IS NULL'
);

$stmt->bind_param(
    'ss',
    $ien,
    $email
);

$stmt->execute();
$stmt->close();

$stmt = $conn->prepare(
    'INSERT INTO student_otps (
        ien_number,
        email,
        otp_hash,
        expires_at,
        attempts,
        request_ip
     )
     VALUES (
        ?,
        ?,
        ?,
        DATE_ADD(NOW(), INTERVAL ? MINUTE),
        0,
        ?
     )'
);

$stmt->bind_param(
    'sssis',
    $ien,
    $email,
    $hash,
    $expiryMinutes,
    $ip
);

$stmt->execute();

$otpId = (int) $stmt->insert_id;

$stmt->close();

$_SESSION['otp_pending'] = true;
$_SESSION['otp_pending_ien'] = $ien;
$_SESSION['otp_pending_email'] = $email;
$_SESSION['otp_pending_started'] = time();
$_SESSION['otp_resend_available_at'] =
    time() + $wait;

$sent = false;

if (
    (bool) cfg(
        'app.dev_otp_mode',
        false
    ) === true
) {
    $_SESSION['dev_otp_hint'] = $otp;
    $sent = true;
} else {
    $sent = send_otp_email(
        $email,
        $otp
    );
}

if (!$sent) {
    /*
     * A failed delivery should not consume the user's resend window.
     * Delete the just-created OTP row so the user can correct mail
     * configuration or retry immediately.
     */

    if ($otpId > 0) {
        $stmt = $conn->prepare(
            'DELETE FROM student_otps
             WHERE id = ?'
        );

        $stmt->bind_param(
            'i',
            $otpId
        );

        $stmt->execute();
        $stmt->close();
    }

    unset(
        $_SESSION['otp_pending'],
        $_SESSION['otp_pending_ien'],
        $_SESSION['otp_pending_email'],
        $_SESSION['otp_pending_started'],
        $_SESSION['otp_resend_available_at'],
        $_SESSION['dev_otp_hint']
    );

    $mailError = mailer_last_error();

    $safeDetail =
        (bool) cfg(
            'app.env',
            'production'
        ) === 'local'
        && $mailError !== ''
        ? ' Details: ' . $mailError
        : '';

    flash(
        'error',
        'The OTP email could not be sent. Check the email configuration.'
        . $safeDetail
    );

    redirect('index.php');
}

flash(
    'success',
    'OTP sent successfully. It expires in '
    . $expiryMinutes
    . ' minutes.'
);

redirect('index.php');
