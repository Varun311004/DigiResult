<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/student-queries.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

require_csrf();

$ien = (string) (
    $_SESSION['otp_pending_ien'] ?? ''
);

$email = (string) (
    $_SESSION['otp_pending_email'] ?? ''
);

$otp = preg_replace(
    '/\D+/',
    '',
    post_string('otp', 6)
);

if (
    $ien === ''
    || !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
    || strlen($otp) !== 6
) {
    flash(
        'error',
        'Your verification session is incomplete. Please request a new OTP.'
    );

    redirect('index.php');
}

$conn = db();

$maxAttempts = (int) cfg(
    'security.otp_max_attempts',
    5
);

$stmt = $conn->prepare(
    'SELECT
        id,
        otp_hash,
        expires_at,
        attempts,
        (expires_at <= NOW()) AS is_expired
     FROM student_otps
     WHERE ien_number = ?
       AND email = ?
       AND consumed_at IS NULL
     ORDER BY id DESC
     LIMIT 1'
);

$stmt->bind_param(
    'ss',
    $ien,
    $email
);

$stmt->execute();

$otpRow = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

if (!$otpRow) {
    flash(
        'error',
        'This OTP is no longer valid. Please request a new one.'
    );

    unset(
        $_SESSION['otp_pending'],
        $_SESSION['otp_pending_ien'],
        $_SESSION['otp_pending_email'],
        $_SESSION['otp_resend_available_at'],
        $_SESSION['dev_otp_hint']
    );

    redirect('index.php');
}

if ((int) $otpRow['is_expired'] === 1) {
    $stmt = $conn->prepare(
        'UPDATE student_otps
         SET consumed_at = NOW()
         WHERE id = ?'
    );

    $stmt->bind_param(
        'i',
        $otpRow['id']
    );

    $stmt->execute();
    $stmt->close();

    flash(
        'error',
        'The OTP has expired. Please request a new one.'
    );

    unset(
        $_SESSION['otp_pending'],
        $_SESSION['otp_pending_ien'],
        $_SESSION['otp_pending_email'],
        $_SESSION['otp_resend_available_at'],
        $_SESSION['dev_otp_hint']
    );

    redirect('index.php');
}

if ((int) $otpRow['attempts'] >= $maxAttempts) {
    flash(
        'error',
        'Too many incorrect attempts. Please request a new OTP.'
    );

    unset(
        $_SESSION['otp_pending'],
        $_SESSION['otp_pending_ien'],
        $_SESSION['otp_pending_email'],
        $_SESSION['otp_resend_available_at'],
        $_SESSION['dev_otp_hint']
    );

    redirect('index.php');
}

if (!password_verify(
    $otp,
    (string) $otpRow['otp_hash']
)) {
    $stmt = $conn->prepare(
        'UPDATE student_otps
         SET attempts = attempts + 1,
             consumed_at = CASE
                 WHEN attempts + 1 >= ? THEN NOW()
                 ELSE NULL
             END
         WHERE id = ?'
    );

    $stmt->bind_param(
        'ii',
        $maxAttempts,
        $otpRow['id']
    );

    $stmt->execute();
    $stmt->close();

    $left = max(
        0,
        $maxAttempts
        - (
            (int) $otpRow['attempts']
            + 1
        )
    );

    flash(
        'error',
        $left > 0
            ? 'Incorrect OTP. Attempts remaining: ' . $left
            : 'Too many incorrect attempts. Please request a new OTP.'
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
        'Student data is no longer available. Please contact the institute.'
    );

    redirect('index.php');
}

$stmt = $conn->prepare(
    'UPDATE student_otps
     SET consumed_at = NOW()
     WHERE id = ?'
);

$stmt->bind_param(
    'i',
    $otpRow['id']
);

$stmt->execute();
$stmt->close();

session_regenerate_id(true);

$_SESSION['student_authenticated'] = true;
$_SESSION['student_id'] = (int) $student['id'];
$_SESSION['student_ien'] = (string) $student['ien_number'];
$_SESSION['student_email'] = (string) $student['email'];
$_SESSION['student_name'] = trim(
    (string) $student['first_name']
    . ' '
    . (string) $student['last_name']
);
$_SESSION['student_login_time'] = time();

unset(
    $_SESSION['otp_pending'],
    $_SESSION['otp_pending_ien'],
    $_SESSION['otp_pending_email'],
    $_SESSION['otp_pending_started'],
    $_SESSION['otp_resend_available_at'],
    $_SESSION['dev_otp_hint']
);

flash(
    'success',
    'Identity verified successfully.'
);

redirect('student/dashboard.php');
