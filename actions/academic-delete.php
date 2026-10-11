<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/academic/index.php');
}

require_csrf();

$type = post_string('type', 20);
$id = safe_int($_POST['id'] ?? null);

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

if (!isset($map[$type]) || !$id) {
    flash('error', 'Invalid academic item.');
    redirect('admin/academic/index.php');
}

$table = $map[$type]['table'];
$column = $map[$type]['col'];

$stmt = db()->prepare(
    "SELECT `$column` AS label
     FROM `$table`
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param('i', $id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

if (!$row) {
    flash('error', 'Academic item not found.');
    redirect('admin/academic/index.php');
}

$value = (string) $row['label'];
$used = 0;
$conn = db();

if ($type === 'year') {
    $year = (int) $value;

    $query = $conn->prepare(
        'SELECT
            (SELECT COUNT(*) FROM students WHERE year = ?)
            + (SELECT COUNT(*) FROM results WHERE year = ?)
            + (SELECT COUNT(*) FROM result_publications WHERE year = ?)
            AS c'
    );

    $query->bind_param(
        'iss',
        $year,
        $value,
        $value
    );
} else {
    $studentResultColumn = match ($type) {
        'batch' => 'batch',
        'semester' => 'semester',
        default => 'department',
    };

    $academicColumn = match ($type) {
        'batch' => 'Batch',
        'semester' => 'Semester',
        default => 'Department',
    };

    $query = $conn->prepare(
        'SELECT
            (SELECT COUNT(*) FROM students WHERE '
            . $studentResultColumn
            . ' = ?)
            + (SELECT COUNT(*) FROM subjects WHERE `'
            . $academicColumn
            . '` = ?)
            + (SELECT COUNT(*) FROM results WHERE '
            . $studentResultColumn
            . ' = ?)
            + (SELECT COUNT(*) FROM result_publications WHERE '
            . $studentResultColumn
            . ' = ?)
            AS c'
    );

    $query->bind_param(
        'ssss',
        $value,
        $value,
        $value,
        $value
    );
}

$query->execute();

$used = (int) (
    $query->get_result()->fetch_assoc()['c'] ?? 0
);

$query->close();

if ($used > 0) {
    flash(
        'error',
        'This ' . $type . ' is in use by existing records and cannot be deleted.'
    );

    redirect('admin/academic/index.php');
}

$delete = $conn->prepare(
    "DELETE FROM `$table`
     WHERE id = ?"
);

$delete->bind_param('i', $id);
$delete->execute();
$delete->close();

audit(
    'academic_value_deleted',
    (int) $_SESSION['admin_id'],
    $type . ': ' . $value
);

flash(
    'success',
    ucfirst($type) . ' deleted.'
);

redirect('admin/academic/index.php');
