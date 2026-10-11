<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/forgot-password.php');
}

require_csrf();

$token = trim(
    (string) (
        $_POST['token'] ?? ''
    )
);

$newPassword = (string) (
    $_POST['new_password'] ?? ''
);

$confirmPassword = (string) (
    $_POST['confirm_password'] ?? ''
);

if ($token === '') {
    flash(
        'error',
        'The password reset link is invalid.'
    );

    redirect('admin/forgot-password.php');
}

if (
    strlen($newPassword) < 10
    || $newPassword !== $confirmPassword
) {
    flash(
        'error',
        'Passwords must match and contain at least 10 characters.'
    );

    redirect(
        'admin/reset-password.php?token='
        . rawurlencode($token)
    );
}

$tokenHash = hash(
    'sha256',
    $token
);

/*
|--------------------------------------------------------------------------
| Validate token
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare(
    'SELECT
        id,
        admin_id,
        email,
        expires_at,
        used_at
     FROM admin_password_resets
     WHERE token_hash = ?
       AND used_at IS NULL
       AND expires_at > NOW()
     LIMIT 1'
);

$stmt->bind_param(
    's',
    $tokenHash
);

$stmt->execute();

$reset = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

$isValid = $reset !== null;

if (!$isValid) {
    flash(
        'error',
        'This password reset link is invalid or has expired. Please request a new one.'
    );

    redirect('admin/forgot-password.php');
}

/*
|--------------------------------------------------------------------------
| Update password
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

$adminId = (int) $reset['admin_id'];

$stmt = db()->prepare(
    'UPDATE admincreds
     SET password = ?
     WHERE id = ?'
);

$stmt->bind_param(
    'si',
    $passwordHash,
    $adminId
);

$stmt->execute();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Consume token
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare(
    'UPDATE admin_password_resets
     SET used_at = NOW()
     WHERE id = ?'
);

$resetId = (int) $reset['id'];

$stmt->bind_param(
    'i',
    $resetId
);

$stmt->execute();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Clean other reset tokens
|--------------------------------------------------------------------------
*/

$stmt = db()->prepare(
    'DELETE FROM admin_password_resets
     WHERE admin_id = ?
       AND id <> ?'
);

$stmt->bind_param(
    'ii',
    $adminId,
    $resetId
);

$stmt->execute();
$stmt->close();

audit(
    'admin_password_reset_completed',
    $adminId,
    'Administrator password reset completed.'
);

/*
|--------------------------------------------------------------------------
| End any current session
|--------------------------------------------------------------------------
*/

if (
    is_admin_logged_in()
    && (int) $_SESSION['admin_id'] === $adminId
) {
    logout_all();
}

flash(
    'success',
    'Your administrator password has been updated. Please sign in with your new password.'
);

redirect('admin/login.php');
