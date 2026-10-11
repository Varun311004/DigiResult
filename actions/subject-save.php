<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/academic/index.php');
}

require_csrf();

$subject = post_string('subject', 150);
$batch = post_string('batch', 50);
$semester = post_string('semester', 50);
$department = post_string('department', 100);

$tw = isset($_POST['tw']) ? '1' : '';
$pr = isset($_POST['pr']) ? '1' : '';
$ese = isset($_POST['ese']) ? '1' : '';

if (
    $subject === ''
    || $batch === ''
    || $semester === ''
    || $department === ''
) {
    flash(
        'error',
        'Complete the subject form.'
    );

    redirect('admin/academic/index.php');
}

$stmt = db()->prepare(
    'INSERT INTO subjects (
        Subject,
        tw_checked,
        pr_checked,
        ese_checked,
        Batch,
        Semester,
        Department
     )
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);

$stmt->bind_param(
    'sssssss',
    $subject,
    $tw,
    $pr,
    $ese,
    $batch,
    $semester,
    $department
);

$ok = $stmt->execute();

$stmt->close();

if (!$ok) {
    flash(
        'error',
        'The subject could not be added. It may already exist for this academic context.'
    );

    redirect('admin/academic/index.php');
}

audit(
    'subject_created',
    (int) $_SESSION['admin_id'],
    $subject
    . ' · '
    . $batch
    . ' · '
    . $semester
    . ' · '
    . $department
);

flash(
    'success',
    'Subject configuration added.'
);

redirect('admin/academic/index.php');
