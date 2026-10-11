<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/academic/index.php');
}

require_csrf();

$id = safe_int(
    $_POST['id'] ?? null
);

if (!$id) {
    flash(
        'error',
        'Invalid subject configuration.'
    );

    redirect('admin/academic/index.php');
}

$conn = db();

$find = $conn->prepare(
    'SELECT Subject, Batch, Semester, Department
     FROM subjects
     WHERE id = ?
     LIMIT 1'
);

$find->bind_param(
    'i',
    $id
);

$find->execute();

$subject = $find
    ->get_result()
    ->fetch_assoc() ?: null;

$find->close();

if (!$subject) {
    flash(
        'error',
        'Subject configuration not found.'
    );

    redirect('admin/academic/index.php');
}

$used = $conn->prepare(
    "SELECT COUNT(*) AS c
     FROM results
     WHERE batch = ?
       AND semester = ?
       AND department = ?
       AND (
            subject = ?
            OR REPLACE(subject, '_', ' ') = ?
       )"
);

$subjectName = (string) $subject['Subject'];

$used->bind_param(
    'sssss',
    $subject['Batch'],
    $subject['Semester'],
    $subject['Department'],
    $subjectName,
    $subjectName
);

$used->execute();

$count = (int) (
    $used
        ->get_result()
        ->fetch_assoc()['c']
    ?? 0
);

$used->close();

if ($count > 0) {
    flash(
        'error',
        'This subject is already used by result records and cannot be deleted.'
    );

    redirect('admin/academic/index.php');
}

$delete = $conn->prepare(
    'DELETE FROM subjects
     WHERE id = ?'
);

$delete->bind_param(
    'i',
    $id
);

$ok = $delete->execute();

$delete->close();

if (!$ok) {
    flash(
        'error',
        'The subject configuration could not be deleted.'
    );

    redirect('admin/academic/index.php');
}

audit(
    'subject_deleted',
    (int) $_SESSION['admin_id'],
    'Subject ID ' . $id . ' · ' . $subjectName
);

flash(
    'success',
    'Subject configuration deleted.'
);

redirect('admin/academic/index.php');
