<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/login.php');
}

require_csrf();

$email = strtolower(
    post_string('email', 255)
);

$password = (string) (
    $_POST['password'] ?? ''
);

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $password === ''
) {
    flash(
        'error',
        'Enter a valid administrator email and password.'
    );

    redirect('admin/login.php');
}

$stmt = db()->prepare(
    'SELECT id, user_id, password
     FROM admincreds
     WHERE LOWER(user_id) = LOWER(?)
     LIMIT 1'
);

$stmt->bind_param('s', $email);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

$ok = false;

if ($row) {
    $stored = (string) $row['password'];

    if (password_get_info($stored)['algo'] !== 0) {
        $ok = password_verify(
            $password,
            $stored
        );
    } else {
        $ok = hash_equals(
            $stored,
            $password
        );

        if ($ok) {
            $new = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $update = db()->prepare(
                'UPDATE admincreds
                 SET password = ?
                 WHERE id = ?'
            );

            $update->bind_param(
                'si',
                $new,
                $row['id']
            );

            $update->execute();
            $update->close();
        }
    }
}

if (!$ok) {
    audit(
        'admin_login_failed',
        null,
        'Invalid administrator login for '
        . substr($email, 0, 120)
    );

    flash(
        'error',
        'Invalid administrator credentials.'
    );

    redirect('admin/login.php');
}

session_regenerate_id(true);

$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_id'] = (int) $row['id'];
$_SESSION['admin_user'] = (string) $row['user_id'];
$_SESSION['admin_login_time'] = time();

audit(
    'admin_login',
    (int) $row['id'],
    'Successful admin login'
);

flash(
    'success',
    'Welcome back.'
);

redirect('admin/dashboard.php');
