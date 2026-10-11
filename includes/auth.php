<?php

declare(strict_types=1);

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_authenticated'])
        && !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        flash(
            'error',
            'Please sign in as an administrator to continue.'
        );

        redirect('admin/login.php');
    }

    $maxAge = 8 * 60 * 60;

    if (
        isset($_SESSION['admin_login_time'])
        && time() - (int) $_SESSION['admin_login_time'] > $maxAge
    ) {
        logout_all();

        flash(
            'error',
            'Your administrator session expired for security.'
        );

        redirect('admin/login.php');
    }
}

function is_student_logged_in(): bool
{
    return !empty($_SESSION['student_authenticated'])
        && !empty($_SESSION['student_id']);
}

function require_student(): void
{
    if (!is_student_logged_in()) {
        flash(
            'error',
            'Your student session has expired. Please verify your email again.'
        );

        redirect('index.php');
    }

    $maxAge = 30 * 60;

    if (
        isset($_SESSION['student_login_time'])
        && time() - (int) $_SESSION['student_login_time'] > $maxAge
    ) {
        unset(
            $_SESSION['student_authenticated'],
            $_SESSION['student_id'],
            $_SESSION['student_ien'],
            $_SESSION['student_email']
        );

        flash(
            'error',
            'Your session expired for security.'
        );

        redirect('index.php');
    }

    $_SESSION['student_login_time'] =
        $_SESSION['student_login_time'] ?? time();
}

function logout_all(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}
