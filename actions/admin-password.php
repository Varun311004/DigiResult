<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/account.php');
}

require_csrf();

$currentPassword = (string) (
    $_POST['current_password'] ?? ''
);

$newPassword = (string) (
    $_POST['new_password'] ?? ''
);

$confirmPassword = (string) (
    $_POST['confirm_password'] ?? ''
);

if (
    strlen($newPassword) < 10
    || $newPassword !== $confirmPassword
) {
    flash(
        'error',
        'New passwords must match and be at least 10 characters.'
    );

    redirect('admin/account.php');
}

$adminId = (int) $_SESSION['admin_id'];

$stmt = db()->prepare(
    'SELECT password
     FROM admincreds
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $adminId);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

if (!$row) {
    flash(
        'error',
        'Administrator account was not found.'
    );

    redirect('admin/account.php');
}

$stored = (string) $row['password'];

$valid = password_get_info($stored)['algo'] !== 0
    ? password_verify(
        $currentPassword,
        $stored
    )
    : hash_equals(
        $stored,
        $currentPassword
    );

if (!$valid) {
    flash(
        'error',
        'Current password is incorrect.'
    );

    redirect('admin/account.php');
}

$hash = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
);

$stmt = db()->prepare(
    'UPDATE admincreds
     SET password = ?
     WHERE id = ?'
);

$stmt->bind_param(
    'si',
    $hash,
    $adminId
);

$stmt->execute();
$stmt->close();

audit(
    'admin_password_changed',
    $adminId,
    'Password updated'
);

flash(
    'success',
    'Administrator password updated.'
);

redirect('admin/account.php');
