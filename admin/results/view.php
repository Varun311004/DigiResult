<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

$year = get_string('year', 10);
$batch = get_string('batch', 50);
$semester = get_string('semester', 50);
$department = get_string('department', 100);

$students = students_for_context(
    $year,
    $batch,
    $semester,
    $department
);

$rows = result_rows_for_context(
    $year,
    $batch,
    $semester,
    $department
);

$byStudent = [];

foreach ($rows as $row) {
    $byStudent[(string) $row['ien_number']][] = $row;
}

$query = http_build_query([
    'year' => $year,
    'batch' => $batch,
    'semester' => $semester,
    'department' => $department,
]);

page_start(
    'Result Preview',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('results'); ?>

    <div class="admin-main">
        <?php admin_topbar('Result Preview'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <div class="toolbar">
                <div>
                    <span class="eyebrow">
                        READ-ONLY PREVIEW
                    </span>

                    <p class="section-copy preview-context">
                        <?= e($year) ?>
                        ·
                        <?= e($batch) ?>
                        ·
                        <?= e($semester) ?>
                        ·
                        <?= e($department) ?>
                    </p>
                </div>

                <a
                    class="btn btn-secondary"
                    href="<?= e(
                        dr_url(
                            'admin/results/index.php?'
                            . $query
                        )
                    ) ?>"
                >
                    Back
                </a>
            </div>

            <?php foreach ($students as $student): ?>
                <section class="card card-pad preview-student-card">
                    <h2 class="section-heading">
                        <?= e(
                            $student['first_name']
                            . ' '
                            . $student['last_name']
                        ) ?>

                        <span class="muted preview-student-ien">
                            ·
                            <?= e($student['ien_number']) ?>
                        </span>
                    </h2>

                    <?php
                    $studentRows =
                        $byStudent[
                            (string) $student['ien_number']
                        ] ?? [];
                    ?>

                    <?php if (!$studentRows): ?>

                        <div class="empty">
                            No result rows.
                        </div>

                    <?php else: ?>

                        <div class="table-wrap">
                            <table class="table table-wide-results">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>TW</th>
                                        <th>PR</th>
                                        <th>ESE</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($studentRows as $row): ?>
                                        <?php
                                        $total =
                                            (int) ($row['TW_marks'] ?? 0)
                                            + (int) ($row['PR_marks'] ?? 0)
                                            + (int) ($row['ESE_marks'] ?? 0);
                                        ?>

                                        <tr>
                                            <td>
                                                <?= e(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        (string) $row['subject']
                                                    )
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $row['TW_marks'] ?? '—'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $row['PR_marks'] ?? '—'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $row['ESE_marks'] ?? '—'
                                                ) ?>
                                            </td>

                                            <td>
                                                <strong><?= e($total) ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
</div>

<?php page_end(); ?>
