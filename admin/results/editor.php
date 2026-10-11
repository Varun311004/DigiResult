<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

$year = get_string('year', 10);
$batch = get_string('batch', 50);
$semester = get_string('semester', 50);
$department = get_string('department', 100);

if (
    $year === ''
    || $batch === ''
    || $semester === ''
    || $department === ''
) {
    flash(
        'error',
        'Select an academic context first.'
    );

    redirect('admin/results/index.php');
}

$students = students_for_context(
    $year,
    $batch,
    $semester,
    $department
);

$subjects = subjects_for_context(
    $batch,
    $semester,
    $department
);

$stored = result_rows_for_context(
    $year,
    $batch,
    $semester,
    $department
);

$byStudent = [];

foreach ($stored as $result) {
    $byStudent[
        (string) $result['ien_number']
    ][
        normalize_subject((string) $result['subject'])
    ] = $result;
}

$query = http_build_query([
    'year' => $year,
    'batch' => $batch,
    'semester' => $semester,
    'department' => $department,
]);

page_start(
    'Result Editor',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('results'); ?>

    <div class="admin-main">
        <?php admin_topbar('Result Editor'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <div class="toolbar">
                <div>
                    <span class="eyebrow">
                        MARK ENTRY
                    </span>

                    <h2 class="section-heading">
                        <?= e($year) ?>
                        ·
                        <?= e($batch) ?>
                        ·
                        <?= e($semester) ?>
                        ·
                        <?= e($department) ?>
                    </h2>

                    <p class="section-copy editor-copy">
                        All TW, PR and ESE fields are editable. Save one
                        student/subject row at a time; leave a component
                        blank to store NULL.
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

            <?php if (!$students): ?>

                <section class="card empty">
                    No students match this academic context.
                </section>

            <?php elseif (!$subjects): ?>

                <section class="card empty">
                    No subjects are configured for this batch/semester/department.
                </section>

            <?php else: ?>

                <div class="result-grid">
                    <?php foreach ($students as $student): ?>
                        <section class="card student-result-card">
                            <div class="student-result-head">
                                <div>
                                    <strong>
                                        <?= e(
                                            $student['first_name']
                                            . ' '
                                            . $student['last_name']
                                        ) ?>
                                    </strong>

                                    <div class="muted editor-student-ien">
                                        IEN
                                        <?= e($student['ien_number']) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="marks-scroll">
                                <div class="marks-grid marks-header">
                                    <div>Subject</div>
                                    <div>TW</div>
                                    <div>PR</div>
                                    <div>ESE</div>
                                    <div>Status</div>
                                    <div>Action</div>
                                </div>

                                <?php foreach ($subjects as $subject): ?>
                                    <?php
                                    $key = normalize_subject(
                                        (string) $subject['Subject']
                                    );

                                    $result =
                                        $byStudent[
                                            (string) $student['ien_number']
                                        ][$key] ?? null;

                                    $tw = $result['TW_marks'] ?? '';
                                    $pr = $result['PR_marks'] ?? '';
                                    $ese = $result['ESE_marks'] ?? '';

                                    $twId =
                                        'tw-'
                                        . $student['ien_number']
                                        . '-'
                                        . md5(
                                            $subject['Subject']
                                        );

                                    $prId =
                                        'pr-'
                                        . $student['ien_number']
                                        . '-'
                                        . md5(
                                            $subject['Subject']
                                        );

                                    $eseId =
                                        'ese-'
                                        . $student['ien_number']
                                        . '-'
                                        . md5(
                                            $subject['Subject']
                                        );
                                    ?>

                                    <form
                                        method="post"
                                        action="<?= e(
                                            dr_url(
                                                'actions/result-save.php'
                                            )
                                        ) ?>"
                                        class="marks-grid result-mark-row"
                                    >
                                        <input
                                            type="hidden"
                                            name="_csrf"
                                            value="<?= e(csrf_token()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="ien_number"
                                            value="<?= e(
                                                $student['ien_number']
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="subject"
                                            value="<?= e(
                                                $subject['Subject']
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="year"
                                            value="<?= e($year) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="batch"
                                            value="<?= e($batch) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="semester"
                                            value="<?= e(
                                                $semester
                                            ) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="department"
                                            value="<?= e(
                                                $department
                                            ) ?>"
                                        >

                                        <div class="subject-name-cell">
                                            <strong>
                                                <?= e(
                                                    $subject['Subject']
                                                ) ?>
                                            </strong>
                                        </div>

                                        <div>
                                            <label
                                                class="sr-only"
                                                for="<?= e($twId) ?>"
                                            >
                                                TW marks
                                            </label>

                                            <input
                                                class="input mark-input"
                                                id="<?= e($twId) ?>"
                                                type="number"
                                                name="tw"
                                                min="0"
                                                max="100"
                                                step="1"
                                                value="<?= e($tw) ?>"
                                                placeholder="—"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="sr-only"
                                                for="<?= e($prId) ?>"
                                            >
                                                PR marks
                                            </label>

                                            <input
                                                class="input mark-input"
                                                id="<?= e($prId) ?>"
                                                type="number"
                                                name="pr"
                                                min="0"
                                                max="100"
                                                step="1"
                                                value="<?= e($pr) ?>"
                                                placeholder="—"
                                            >
                                        </div>

                                        <div>
                                            <label
                                                class="sr-only"
                                                for="<?= e($eseId) ?>"
                                            >
                                                ESE marks
                                            </label>

                                            <input
                                                class="input mark-input"
                                                id="<?= e($eseId) ?>"
                                                type="number"
                                                name="ese"
                                                min="0"
                                                max="100"
                                                step="1"
                                                value="<?= e($ese) ?>"
                                                placeholder="—"
                                            >
                                        </div>

                                        <div class="result-status-cell">
                                            <?=
                                            $result
                                                ? '<span class="badge badge-live">Saved</span>'
                                                : '<span class="badge badge-off">Not saved</span>'
                                            ?>
                                        </div>

                                        <div class="result-action-cell">
                                            <button class="btn btn-primary btn-sm">
                                                Save
                                            </button>
                                        </div>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </main>
    </div>
</div>

<?php page_end(); ?>
