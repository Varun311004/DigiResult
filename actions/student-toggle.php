<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/students/index.php');
}

require_csrf();

$id = safe_int(
    $_POST['id'] ?? null
);

$status = safe_int(
    $_POST['status'] ?? null,
    -1
);

if (
    !$id
    || !in_array(
        $status,
        [0, 1],
        true
    )
) {
    flash(
        'error',
        'Invalid student status request.'
    );

    redirect('admin/students/index.php');
}

$conn = db();

$stmt = $conn->prepare(
    'SELECT first_name, last_name, ien_number
     FROM students
     WHERE id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

if (!$student) {
    flash(
        'error',
        'Student not found.'
    );

    redirect('admin/students/index.php');
}

$update = $conn->prepare(
    'UPDATE students
     SET is_active = ?
     WHERE id = ?'
);

$update->bind_param(
    'ii',
    $status,
    $id
);

$ok = $update->execute();

$update->close();

if (!$ok) {
    flash(
        'error',
        'The student status could not be updated.'
    );

    redirect('admin/students/index.php');
}

audit(
    $status
        ? 'student_reactivated'
        : 'student_deactivated',
    (int) $_SESSION['admin_id'],
    'Student ' . $student['ien_number']
);

flash(
    'success',
    $status
        ? 'Student reactivated.'
        : 'Student deactivated.'
);

redirect('admin/students/index.php');
