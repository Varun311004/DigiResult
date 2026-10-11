<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/academic/index.php');
}

require_csrf();

$type = post_string('type', 20);
$value = post_string('value', 100);

$map = [
    'year' => [
        'table' => 'year',
        'col' => 'Year',
    ],
    'batch' => [
        'table' => 'batch',
        'col' => 'Batch',
    ],
    'semester' => [
        'table' => 'semester',
        'col' => 'Semester',
    ],
    'department' => [
        'table' => 'department',
        'col' => 'Department',
    ],
];

if (!isset($map[$type]) || $value === '') {
    flash('error', 'Invalid academic value.');
    redirect('admin/academic/index.php');
}

if (
    $type === 'year'
    && (
        !ctype_digit($value)
        || (int) $value < 2000
        || (int) $value > 2100
    )
) {
    flash(
        'error',
        'Enter a valid academic year between 2000 and 2100.'
    );

    redirect('admin/academic/index.php');
}

$table = $map[$type]['table'];
$column = $map[$type]['col'];
$valueSql = $type === 'year' ? (int) $value : $value;

$conn = db();

$exists = $conn->prepare(
    "SELECT id
     FROM `$table`
     WHERE `$column` = ?
     LIMIT 1"
);

$exists->bind_param(
    $type === 'year' ? 'i' : 's',
    $valueSql
);

$exists->execute();

$row = $exists->get_result()->fetch_assoc() ?: null;

$exists->close();

if ($row) {
    flash('warning', 'That value already exists.');
    redirect('admin/academic/index.php');
}

$stmt = $conn->prepare(
    "INSERT INTO `$table` (`$column`)
     VALUES (?)"
);

$stmt->bind_param(
    $type === 'year' ? 'i' : 's',
    $valueSql
);

$ok = $stmt->execute();

$stmt->close();

if (!$ok) {
    flash(
        'error',
        'The academic value could not be added. It may already exist or the database rejected it.'
    );

    redirect('admin/academic/index.php');
}

audit(
    'academic_value_added',
    (int) $_SESSION['admin_id'],
    $type . ': ' . $value
);

flash(
    'success',
    ucfirst($type) . ' added.'
);

redirect('admin/academic/index.php');
