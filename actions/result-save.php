<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/admin-queries.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/results/index.php');
}

require_csrf();

$ien = post_string('ien_number', 20);
$subject = post_string('subject', 150);
$year = post_string('year', 10);
$batch = post_string('batch', 50);
$semester = post_string('semester', 50);
$department = post_string('department', 100);

$query =
    'year=' . rawurlencode($year)
    . '&batch=' . rawurlencode($batch)
    . '&semester=' . rawurlencode($semester)
    . '&department=' . rawurlencode($department);

$to = 'admin/results/editor.php?' . $query;

if (
    $ien === ''
    || $subject === ''
    || $year === ''
    || $batch === ''
    || $semester === ''
    || $department === ''
) {
    flash(
        'error',
        'Incomplete result context.'
    );

    redirect($to);
}

$marks = [];

foreach (['tw', 'pr', 'ese'] as $key) {
    $value = trim(
        (string) ($_POST[$key] ?? '')
    );

    if (
        $value !== ''
        && (
            !ctype_digit($value)
            || (int) $value < 0
            || (int) $value > 100
        )
    ) {
        flash(
            'error',
            'Marks must be between 0 and 100.'
        );

        redirect($to);
    }

    $marks[$key] = $value === ''
        ? null
        : (int) $value;
}

$conn = db();

$subjectCheck = $conn->prepare(
    'SELECT id
     FROM subjects
     WHERE Subject = ?
       AND Batch = ?
       AND Semester = ?
       AND Department = ?
     LIMIT 1'
);

$subjectCheck->bind_param(
    'ssss',
    $subject,
    $batch,
    $semester,
    $department
);

$subjectCheck->execute();

$subjectOk = $subjectCheck
    ->get_result()
    ->num_rows > 0;

$subjectCheck->close();

if (!$subjectOk) {
    flash(
        'error',
        'Selected subject is not configured for this academic context.'
    );

    redirect($to);
}

$check = $conn->prepare(
    'SELECT id, first_name, last_name
     FROM students
     WHERE ien_number = ?
       AND year = ?
       AND batch = ?
       AND semester = ?
       AND department = ?
     LIMIT 1'
);

$check->bind_param(
    'sssss',
    $ien,
    $year,
    $batch,
    $semester,
    $department
);

$check->execute();

$student = $check->get_result()->fetch_assoc() ?: null;

$check->close();

if (!$student) {
    flash(
        'error',
        'Student does not belong to the selected academic context.'
    );

    redirect($to);
}

$find = $conn->prepare(
    "SELECT id, subject
     FROM results
     WHERE ien_number = ?
       AND year = ?
       AND batch = ?
       AND semester = ?
       AND department = ?
       AND (
            subject = ?
            OR REPLACE(subject, '_', ' ') = ?
       )
     LIMIT 1"
);

$find->bind_param(
    'sssssss',
    $ien,
    $year,
    $batch,
    $semester,
    $department,
    $subject,
    $subject
);

$find->execute();

$old = $find->get_result()->fetch_assoc() ?: null;

$find->close();

$tw = $marks['tw'];
$pr = $marks['pr'];
$ese = $marks['ese'];

if ($old) {
    $update = $conn->prepare(
        'UPDATE results
         SET subject = ?,
             TW_marks = ?,
             PR_marks = ?,
             ESE_marks = ?
         WHERE id = ?'
    );

    $update->bind_param(
        'siiii',
        $subject,
        $tw,
        $pr,
        $ese,
        $old['id']
    );

    $update->execute();
    $update->close();

    audit(
        'result_updated',
        (int) $_SESSION['admin_id'],
        'Result ID ' . $old['id'] . ' for ' . $ien
    );

    flash(
        'success',
        'Marks saved.'
    );
} else {
    $insert = $conn->prepare(
        'INSERT INTO results (
            ien_number,
            subject,
            TW_marks,
            PR_marks,
            ESE_marks,
            year,
            batch,
            semester,
            department
         )
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $insert->bind_param(
        'ssiiissss',
        $ien,
        $subject,
        $tw,
        $pr,
        $ese,
        $year,
        $batch,
        $semester,
        $department
    );

    $insert->execute();

    $newId = (int) $insert->insert_id;

    $insert->close();

    audit(
        'result_created',
        (int) $_SESSION['admin_id'],
        'Result ID ' . $newId . ' for ' . $ien
    );

    flash(
        'success',
        'Marks saved.'
    );
}

redirect($to);
