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

if (!$id) {
    flash(
        'error',
        'Invalid student record.'
    );

    redirect('admin/students/index.php');
}

$conn = db();

$stmt = $conn->prepare(
    'SELECT ien_number, first_name, last_name
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

$conn->begin_transaction();

try {
    $deleteResults = $conn->prepare(
        'DELETE FROM results
         WHERE ien_number = ?'
    );

    $deleteResults->bind_param(
        's',
        $student['ien_number']
    );

    $deleteResults->execute();
    $deleteResults->close();

    $deleteStudent = $conn->prepare(
        'DELETE FROM students
         WHERE id = ?'
    );

    $deleteStudent->bind_param(
        'i',
        $id
    );

    $deleteStudent->execute();
    $deleteStudent->close();

    $conn->commit();

    audit(
        'student_deleted',
        (int) $_SESSION['admin_id'],
        'Student ' . $student['ien_number']
    );

    flash(
        'success',
        'Student and linked result records were deleted.'
    );
} catch (Throwable $e) {
    $conn->rollback();

    error_log(
        'DigiResult student delete failed: '
        . $e->getMessage()
    );

    flash(
        'error',
        'The student could not be deleted.'
    );
}

redirect('admin/students/index.php');
