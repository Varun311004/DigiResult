<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/results/index.php');
}

require_csrf();

$year = post_string('year', 10);
$batch = post_string('batch', 50);
$semester = post_string('semester', 50);
$department = post_string('department', 100);
$action = post_string('action', 20);

$back =
    'admin/results/index.php'
    . '?year=' . rawurlencode($year)
    . '&batch=' . rawurlencode($batch)
    . '&semester=' . rawurlencode($semester)
    . '&department=' . rawurlencode($department);

if (
    $year === ''
    || $batch === ''
    || $semester === ''
    || $department === ''
    || !in_array(
        $action,
        ['publish', 'unpublish'],
        true
    )
) {
    flash(
        'error',
        'Invalid publication request.'
    );

    redirect($back);
}

$conn = db();

if ($action === 'publish') {
    $countStmt = $conn->prepare(
        'SELECT COUNT(*) AS c
         FROM results
         WHERE year = ?
           AND batch = ?
           AND semester = ?
           AND department = ?'
    );

    $countStmt->bind_param(
        'ssss',
        $year,
        $batch,
        $semester,
        $department
    );

    $countStmt->execute();

    $count = (int) (
        $countStmt
            ->get_result()
            ->fetch_assoc()['c']
        ?? 0
    );

    $countStmt->close();

    if ($count === 0) {
        flash(
            'error',
            'Add at least one result row before publishing this context.'
        );

        redirect($back);
    }
}

$stmt = $conn->prepare(
    'SELECT id
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

$row = $stmt->get_result()->fetch_assoc() ?: null;

$stmt->close();

$live = $action === 'publish' ? 1 : 0;

if ($row) {
    $adminId = (int) $_SESSION['admin_id'];

    $stmt = $conn->prepare(
        'UPDATE result_publications
         SET is_live = ?,
             published_at = CASE
                 WHEN ? = 1 THEN NOW()
                 ELSE published_at
             END,
             published_by = ?
         WHERE id = ?'
    );

    $stmt->bind_param(
        'iiii',
        $live,
        $live,
        $adminId,
        $row['id']
    );

    $stmt->execute();
    $stmt->close();

    $publicationId = (int) $row['id'];
} else {
    $adminId = (int) $_SESSION['admin_id'];

    $stmt = $conn->prepare(
        'INSERT INTO result_publications (
            year,
            batch,
            semester,
            department,
            is_live,
            published_at,
            published_by
         )
         VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            CASE
                WHEN ? = 1 THEN NOW()
                ELSE NULL
            END,
            ?
         )'
    );

    $stmt->bind_param(
        'ssssiii',
        $year,
        $batch,
        $semester,
        $department,
        $live,
        $live,
        $adminId
    );

    $stmt->execute();

    $publicationId = (int) $stmt->insert_id;

    $stmt->close();
}

audit(
    $live ? 'results_published' : 'results_unpublished',
    $adminId,
    'Publication '
    . $publicationId
    . ' for '
    . $year
    . '/'
    . $batch
    . '/'
    . $semester
    . '/'
    . $department
);

flash(
    'success',
    $live
        ? 'Results published successfully.'
        : 'Results unpublished successfully.'
);

redirect($back);
