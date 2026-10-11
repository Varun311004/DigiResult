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

$ien = post_string('ien_number', 20);
$first = post_string('first_name', 100);
$last = post_string('last_name', 100);
$dob = post_string('date_of_birth', 10);
$gender = post_string('gender', 10);
$email = strtolower(
    post_string('email', 255)
);
$address = post_string('address', 255);
$studentNumber = post_string('student_number', 20);
$year = post_string('year', 10);
$department = post_string('department', 100);
$batch = post_string('batch', 50);
$semester = post_string('semester', 50);

if (
    $ien === ''
    || $first === ''
    || $last === ''
    || $dob === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $address === ''
    || $studentNumber === ''
    || $year === ''
    || $department === ''
    || $batch === ''
    || $semester === ''
) {
    flash(
        'error',
        'Please complete all required fields.'
    );

    redirect(
        $id
            ? 'admin/students/edit.php?id=' . $id
            : 'admin/students/create.php'
    );
}

$conn = db();

$duplicate = $conn->prepare(
    $id
        ? 'SELECT id
           FROM students
           WHERE ien_number = ?
             AND id <> ?
           LIMIT 1'
        : 'SELECT id
           FROM students
           WHERE ien_number = ?
           LIMIT 1'
);

if ($id) {
    $duplicate->bind_param(
        'si',
        $ien,
        $id
    );
} else {
    $duplicate->bind_param(
        's',
        $ien
    );
}

$duplicate->execute();

$exists = $duplicate
    ->get_result()
    ->fetch_assoc();

$duplicate->close();

if ($exists) {
    flash(
        'error',
        'That IEN number is already assigned to another student.'
    );

    redirect(
        $id
            ? 'admin/students/edit.php?id=' . $id
            : 'admin/students/create.php'
    );
}

if ($id) {
    $stmt = $conn->prepare(
        'UPDATE students
         SET ien_number = ?,
             first_name = ?,
             last_name = ?,
             date_of_birth = ?,
             gender = ?,
             email = ?,
             address = ?,
             student_number = ?,
             year = ?,
             department = ?,
             batch = ?,
             semester = ?
         WHERE id = ?'
    );

    $stmt->bind_param(
        'ssssssssisssi',
        $ien,
        $first,
        $last,
        $dob,
        $gender,
        $email,
        $address,
        $studentNumber,
        $year,
        $department,
        $batch,
        $semester,
        $id
    );

    $stmt->execute();
    $stmt->close();

    audit(
        'student_updated',
        (int) $_SESSION['admin_id'],
        'Student ID ' . $id
    );

    flash(
        'success',
        'Student record updated.'
    );
} else {
    $stmt = $conn->prepare(
        'INSERT INTO students (
            ien_number,
            first_name,
            last_name,
            date_of_birth,
            gender,
            email,
            address,
            student_number,
            year,
            department,
            batch,
            semester
         )
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'ssssssssisss',
        $ien,
        $first,
        $last,
        $dob,
        $gender,
        $email,
        $address,
        $studentNumber,
        $year,
        $department,
        $batch,
        $semester
    );

    $stmt->execute();

    $newId = (int) $stmt->insert_id;

    $stmt->close();

    audit(
        'student_created',
        (int) $_SESSION['admin_id'],
        'Student ID ' . $newId
    );

    flash(
        'success',
        'Student record created.'
    );
}

redirect('admin/students/index.php');
