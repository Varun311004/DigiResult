<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function lookup_rows(
    string $table,
    string $column,
    string $orderColumn = 'id'
): array {
    $allowed = [
        'batch',
        'department',
        'semester',
        'year',
    ];

    if (!in_array($table, $allowed, true)) {
        return [];
    }

    $stmt = db()->prepare(
        "SELECT id, `$column` AS label
         FROM `$table`
         ORDER BY `$orderColumn` DESC, id DESC"
    );

    $stmt->execute();

    $rows = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}

function list_students(string $search = ''): array
{
    if ($search === '') {
        $stmt = db()->prepare(
            'SELECT * FROM students ORDER BY id DESC'
        );
        $stmt->execute();
    } else {
        $like = '%' . $search . '%';

        $stmt = db()->prepare(
            'SELECT *
             FROM students
             WHERE CAST(ien_number AS CHAR) LIKE ?
                OR first_name LIKE ?
                OR last_name LIKE ?
                OR email LIKE ?
                OR student_number LIKE ?
             ORDER BY id DESC'
        );

        $stmt->bind_param(
            'sssss',
            $like,
            $like,
            $like,
            $like,
            $like
        );

        $stmt->execute();
    }

    $rows = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}

function context_options(): array
{
    $sql = '
        SELECT DISTINCT
            year,
            batch,
            semester,
            department
        FROM students
        ORDER BY year DESC, batch, semester, department
    ';

    return db()->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function subjects_for_context(
    string $batch,
    string $semester,
    string $department
): array {
    $stmt = db()->prepare(
        'SELECT *
         FROM subjects
         WHERE Batch = ?
           AND Semester = ?
           AND Department = ?
         ORDER BY Subject'
    );

    $stmt->bind_param(
        'sss',
        $batch,
        $semester,
        $department
    );

    $stmt->execute();

    $rows = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}

function students_for_context(
    string $year,
    string $batch,
    string $semester,
    string $department
): array {
    $stmt = db()->prepare(
        'SELECT *
         FROM students
         WHERE year = ?
           AND batch = ?
           AND semester = ?
           AND department = ?
         ORDER BY ien_number'
    );

    $stmt->bind_param(
        'ssss',
        $year,
        $batch,
        $semester,
        $department
    );

    $stmt->execute();

    $rows = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}

function result_rows_for_context(
    string $year,
    string $batch,
    string $semester,
    string $department
): array {
    $stmt = db()->prepare(
        'SELECT *
         FROM results
         WHERE year = ?
           AND batch = ?
           AND semester = ?
           AND department = ?
         ORDER BY ien_number, subject'
    );

    $stmt->bind_param(
        'ssss',
        $year,
        $batch,
        $semester,
        $department
    );

    $stmt->execute();

    $rows = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}

function publication_for_context(
    string $year,
    string $batch,
    string $semester,
    string $department
): ?array {
    $stmt = db()->prepare(
        'SELECT *
         FROM result_publications
         WHERE year = ?
           AND batch = ?
           AND semester = ?
           AND department = ?
         LIMIT 1'
    );

    $stmt->bind_param(
        'ssss',
        $year,
        $batch,
        $semester,
        $department
    );

    $stmt->execute();

    $row = $stmt
        ->get_result()
        ->fetch_assoc()
        ?: null;

    $stmt->close();

    return $row;
}
