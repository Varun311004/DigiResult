<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function page_start(
    string $title,
    string $area = 'public',
    string $active = '',
    array $extraStylesheets = []
): void {
    $full = e($title) . ' · DigiResult';

    $stylesheet = match ($area) {
        'admin' => 'assets/css/admin.css',
        'student' => 'assets/css/student.css',
        default => 'assets/css/auth.css',
    };

    echo '<!doctype html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="theme-color" content="#22b14d">';
    echo '<title>' . $full . '</title>';
    echo '<link rel="stylesheet" href="'
        . e(dr_url('assets/css/app.css'))
        . '">';
    echo '<link rel="stylesheet" href="'
        . e(dr_url($stylesheet))
        . '">';

    foreach ($extraStylesheets as $extraStylesheet) {
        echo '<link rel="stylesheet" href="'
            . e(dr_url((string) $extraStylesheet))
            . '">';
    }

    echo '<script defer src="'
        . e(dr_url('assets/js/app.js'))
        . '"></script>';
    echo '</head>';
    echo '<body class="page-' . e($area) . '">';
}

function public_nav(): void
{
    echo '<header class="topbar public-topbar">';
    echo '<a class="brand" href="'
        . e(dr_url('index.php'))
        . '">';
    echo '<img src="'
        . e(dr_url('assets/images/logo.webp'))
        . '" alt="DigiResult logo">';
    echo '<span>';
    echo '<strong>DigiResult</strong>';
    echo '<small>'
        . e(cfg('app.institute'))
        . '</small>';
    echo '</span>';
    echo '</a>';
    echo '<nav>';
    echo '<a href="'
        . e(dr_url('index.php'))
        . '">Student Portal</a>';
    echo '<a class="nav-pill" href="'
        . e(dr_url('admin/login.php'))
        . '">Admin</a>';
    echo '</nav>';
    echo '</header>';
}

function student_nav(): void
{
    echo '<header class="topbar student-topbar">';
    echo '<a class="brand" href="'
        . e(dr_url('student/dashboard.php'))
        . '">';
    echo '<img src="'
        . e(dr_url('assets/images/logo.webp'))
        . '" alt="DigiResult logo">';
    echo '<span>';
    echo '<strong>DigiResult</strong>';
    echo '<small>Student Result Portal</small>';
    echo '</span>';
    echo '</a>';
    echo '<div class="top-actions">';
    echo '<a class="btn btn-secondary btn-sm" href="'
        . e(dr_url('student/dashboard.php'))
        . '">Dashboard</a>';
    echo '<a class="btn btn-secondary btn-sm" href="'
        . e(dr_url('student/logout.php'))
        . '">Logout</a>';
    echo '</div>';
    echo '</header>';
}

function admin_sidebar(string $active = ''): void
{
    $links = [
        'dashboard' => ['Dashboard', 'admin/dashboard.php'],
        'students' => ['Students', 'admin/students/index.php'],
        'results' => ['Results', 'admin/results/index.php'],
        'academic' => ['Academic Setup', 'admin/academic/index.php'],
        'account' => ['Admin Account', 'admin/account.php'],
        // 'health' => ['System Health', 'admin/health.php'],
    ];

    echo '<aside class="sidebar">';
    echo '<div class="side-brand">';
    echo '<img src="'
        . e(dr_url('assets/images/logo.webp'))
        . '" alt="">';
    echo '<div>';
    echo '<strong>DigiResult</strong>';
    echo '<small>Administration</small>';
    echo '</div>';
    echo '</div>';

    echo '<nav>';

    foreach ($links as $key => [$label, $url]) {
        $activeClass = $active === $key ? ' active' : '';

        echo '<a class="side-link'
            . $activeClass
            . '" href="'
            . e(dr_url($url))
            . '">'
            . e($label)
            . '</a>';
    }

    echo '</nav>';
    echo '<div class="side-footer">';
    echo '<a class="side-link" href="'
        . e(dr_url('admin/logout.php'))
        . '" data-confirm="Are you sure you want to log out of DigiResult?">';
    echo 'Logout';
    echo '</a>';
    echo '</div>';
    echo '</aside>';
}

function admin_topbar(string $title): void
{
    $email = (string) (
        $_SESSION['admin_user'] ?? 'Administrator'
    );

    echo '<header class="admin-topbar">';
    echo '<div>';
    echo '<span class="eyebrow">ADMINISTRATION</span>';
    echo '<h1>' . e($title) . '</h1>';
    echo '</div>';
    echo '<div class="admin-user">';
    echo '<span class="avatar">'
        . e(strtoupper(substr($email, 0, 1)))
        . '</span>';
    echo '<div>';
    echo '<strong>Administrator</strong>';
    echo '<small>' . e($email) . '</small>';
    echo '</div>';
    echo '</div>';
    echo '</header>';
}

function render_flashes(): void
{
    foreach (consume_flash() as $item) {
        $type = in_array(
            $item['type'],
            ['success', 'error', 'warning', 'info'],
            true
        )
            ? $item['type']
            : 'info';

        echo '<div class="flash flash-'
            . e($type)
            . '" data-autohide="5000">';
        echo '<span>' . e($item['message']) . '</span>';
        echo '<button type="button" class="flash-close" aria-label="Close">×</button>';
        echo '</div>';
    }
}

function page_end(): void
{
    echo '</body>';
    echo '</html>';
}
