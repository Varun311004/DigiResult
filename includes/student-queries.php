<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';


function find_student_by_ien_email(string $ien, string $email): ?array
{
    $stmt = db()->prepare(<<<'SQL'
SELECT *
FROM students
WHERE ien_number = ?
  AND LOWER(email) = LOWER(?)
  AND is_active = 1
LIMIT 1
SQL
    );

    $stmt->bind_param('ss', $ien, $email);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc() ?: null;

    $stmt->close();

    return $row;
}


function get_student(int $id): ?array
{
    $stmt = db()->prepare(<<<'SQL'
SELECT *
FROM students
WHERE id = ?
LIMIT 1
SQL
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc() ?: null;

    $stmt->close();

    return $row;
}


function get_published_contexts_for_student(string $ien): array
{
    $stmt = db()->prepare(<<<'SQL'
SELECT rp.*
FROM result_publications rp
WHERE rp.is_live = 1
  AND EXISTS (
      SELECT 1
      FROM results r
      WHERE r.ien_number = ?
        AND r.year = rp.year
        AND r.batch = rp.batch
        AND r.semester = rp.semester
        AND r.department = rp.department
  )
ORDER BY rp.published_at DESC, rp.year DESC
LIMIT 20
SQL
    );

    $stmt->bind_param('s', $ien);
    $stmt->execute();

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}


function get_publication_for_student(int $id, int $publicationId): ?array
{
    $student = get_student($id);

    if (!$student) {
        return null;
    }

    $stmt = db()->prepare(<<<'SQL'
SELECT rp.*
FROM result_publications rp
WHERE rp.id = ?
  AND rp.is_live = 1
  AND EXISTS (
      SELECT 1
      FROM results r
      WHERE r.ien_number = ?
        AND r.year = rp.year
        AND r.batch = rp.batch
        AND r.semester = rp.semester
        AND r.department = rp.department
  )
LIMIT 1
SQL
    );

    $stmt->bind_param(
        'is',
        $publicationId,
        $student['ien_number']
    );

    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc() ?: null;

    $stmt->close();

    return $row;
}


function get_results_for_context(
    string $ien,
    string $year,
    string $batch,
    string $semester,
    string $department
): array {
    $stmt = db()->prepare(<<<'SQL'
SELECT id, subject, TW_marks, PR_marks, ESE_marks
FROM results
WHERE ien_number = ?
  AND year = ?
  AND batch = ?
  AND semester = ?
  AND department = ?
ORDER BY subject
SQL
    );

    $stmt->bind_param(
        'sssss',
        $ien,
        $year,
        $batch,
        $semester,
        $department
    );

    $stmt->execute();

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
}


function get_certificate_rows_for_student(
    string $ien,
    string $year,
    string $batch,
    string $semester,
    string $department
): array {
    /*
     * The subject catalogue is mapped by Batch + Semester + Department.
     * The exact Year is enforced against result rows so marks from another
     * academic year can never be attached to the selected publication.
     */
    $catalogStmt = db()->prepare(<<<'SQL'
SELECT id, Subject, tw_checked, pr_checked, ese_checked
FROM subjects
WHERE Batch = ?
  AND Semester = ?
  AND Department = ?
ORDER BY id ASC, Subject ASC
SQL
    );

    $catalogStmt->bind_param(
        'sss',
        $batch,
        $semester,
        $department
    );

    $catalogStmt->execute();

    $catalogRows = $catalogStmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $catalogStmt->close();


    /*
     * Preserve legacy subjects that were saved in the exact academic result
     * context but are absent from the current subject catalogue.
     */
    $contextStmt = db()->prepare(<<<'SQL'
SELECT subject, MIN(id) AS first_id
FROM results
WHERE year = ?
  AND batch = ?
  AND semester = ?
  AND department = ?
  AND subject IS NOT NULL
  AND TRIM(subject) <> ''
GROUP BY subject
ORDER BY first_id ASC
SQL
    );

    $contextStmt->bind_param(
        'ssss',
        $year,
        $batch,
        $semester,
        $department
    );

    $contextStmt->execute();

    $contextRows = $contextStmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $contextStmt->close();


    /*
     * Merge configured subjects and legacy-only subjects without duplicates.
     */
    $subjects = [];
    $seen = [];

    foreach ($catalogRows as $subject) {
        $name = trim((string) $subject['Subject']);
        $key = normalize_subject($name);

        if ($name === '' || isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;

        $subjects[] = [
            'subject' => $name,
            'tw_checked' => $subject['tw_checked'] ?? '',
            'pr_checked' => $subject['pr_checked'] ?? '',
            'ese_checked' => $subject['ese_checked'] ?? '',
        ];
    }

    foreach ($contextRows as $subject) {
        $name = trim((string) $subject['subject']);
        $key = normalize_subject($name);

        if ($name === '' || isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;

        $subjects[] = [
            'subject' => str_replace('_', ' ', $name),
            'tw_checked' => '',
            'pr_checked' => '',
            'ese_checked' => '',
        ];
    }


    /*
     * Load the selected student's marks for the exact same context.
     */
    $resultStmt = db()->prepare(<<<'SQL'
SELECT subject, TW_marks, PR_marks, ESE_marks
FROM results
WHERE ien_number = ?
  AND year = ?
  AND batch = ?
  AND semester = ?
  AND department = ?
ORDER BY id ASC
SQL
    );

    $resultStmt->bind_param(
        'sssss',
        $ien,
        $year,
        $batch,
        $semester,
        $department
    );

    $resultStmt->execute();

    $resultRows = $resultStmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $resultStmt->close();


    $resultBySubject = [];

    foreach ($resultRows as $result) {
        $key = normalize_subject((string) $result['subject']);
        $resultBySubject[$key] = $result;
    }


    /*
     * IMPORTANT:
     * Every configured subject is returned, even when the student has no
     * marks saved for it yet. Missing marks remain NULL.
     */
    $rows = [];

    foreach ($subjects as $subject) {
        $name = (string) $subject['subject'];
        $key = normalize_subject($name);
        $result = $resultBySubject[$key] ?? null;

        $rows[] = [
            'subject' => $name,
            'TW_marks' => $result['TW_marks'] ?? null,
            'PR_marks' => $result['PR_marks'] ?? null,
            'ESE_marks' => $result['ESE_marks'] ?? null,
            'tw_checked' => $subject['tw_checked'],
            'pr_checked' => $subject['pr_checked'],
            'ese_checked' => $subject['ese_checked'],
            'has_result' => $result !== null,
            'configured' => true,
        ];
    }

    return $rows;
}
