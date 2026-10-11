<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

/*
|--------------------------------------------------------------------------
| Available academic contexts
|--------------------------------------------------------------------------
*/

$contexts = context_options();

/*
|--------------------------------------------------------------------------
| Selected filters
|--------------------------------------------------------------------------
*/

$year = get_string('year', 10);
$batch = get_string('batch', 50);
$semester = get_string('semester', 50);
$department = get_string('department', 100);

/*
|--------------------------------------------------------------------------
| Build unique dropdown values
|--------------------------------------------------------------------------
|
| context_options() returns unique combinations of:
| year + batch + semester + department.
|
| A year can therefore occur in several rows. The dropdowns below need
| distinct values independently.
|
*/

$years = [];
$batches = [];
$semesters = [];
$departments = [];

foreach ($contexts as $context) {
    $contextYear = trim((string) ($context['year'] ?? ''));
    $contextBatch = trim((string) ($context['batch'] ?? ''));
    $contextSemester = trim((string) ($context['semester'] ?? ''));
    $contextDepartment = trim((string) ($context['department'] ?? ''));

    if ($contextYear !== '') {
        $years[] = $contextYear;
    }

    if ($contextBatch !== '') {
        $batches[] = $contextBatch;
    }

    if ($contextSemester !== '') {
        $semesters[] = $contextSemester;
    }

    if ($contextDepartment !== '') {
        $departments[] = $contextDepartment;
    }
}

$years = array_values(array_unique($years));
$batches = array_values(array_unique($batches));
$semesters = array_values(array_unique($semesters));
$departments = array_values(array_unique($departments));

/*
|--------------------------------------------------------------------------
| Sort values
|--------------------------------------------------------------------------
*/

usort(
    $years,
    static function (string $a, string $b): int {
        return (int) $b <=> (int) $a;
    }
);

natcasesort($batches);
natcasesort($semesters);
natcasesort($departments);

$batches = array_values($batches);
$semesters = array_values($semesters);
$departments = array_values($departments);

/*
|--------------------------------------------------------------------------
| Check complete context
|--------------------------------------------------------------------------
*/

$selected =
    $year !== ''
    && $batch !== ''
    && $semester !== ''
    && $department !== '';

/*
|--------------------------------------------------------------------------
| Load result context
|--------------------------------------------------------------------------
*/

$publication = null;
$rows = [];

if ($selected) {
    $publication = publication_for_context(
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
}

/*
|--------------------------------------------------------------------------
| Query string for secondary actions
|--------------------------------------------------------------------------
*/

$contextQuery = http_build_query([
    'year' => $year,
    'batch' => $batch,
    'semester' => $semester,
    'department' => $department,
]);

page_start('Results', 'admin');

?>

<div class="admin-layout">

<?php
admin_sidebar('results');
?>

    <div class="admin-main">

<?php
admin_topbar('Results');
render_flashes();
?>

<main class="admin-content">

    <div class="toolbar">

        <div>
            <span class="eyebrow">RESULT MANAGEMENT</span>

            <p class="section-copy results-context-copy">
                Select an academic context to enter, review and publish marks.
            </p>
        </div>

    </div>

    <section class="card filters">

        <form method="get">

            <div class="form-grid">

                <div class="field">
                    <label class="form-label" for="result-year">
                        Year
                    </label>

                    <select
                        class="select"
                        id="result-year"
                        name="year"
                        required
                    >
                        <option value="">Select</option>

                        <?php foreach ($years as $optionYear): ?>
                            <option
                                value="<?= e($optionYear) ?>"
                                <?= $year === $optionYear ? 'selected' : '' ?>
                            >
                                <?= e($optionYear) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label class="form-label" for="result-batch">
                        Batch
                    </label>

                    <select
                        class="select"
                        id="result-batch"
                        name="batch"
                        required
                    >
                        <option value="">Select</option>

                        <?php foreach ($batches as $optionBatch): ?>
                            <option
                                value="<?= e($optionBatch) ?>"
                                <?= $batch === $optionBatch ? 'selected' : '' ?>
                            >
                                <?= e($optionBatch) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label class="form-label" for="result-semester">
                        Semester
                    </label>

                    <select
                        class="select"
                        id="result-semester"
                        name="semester"
                        required
                    >
                        <option value="">Select</option>

                        <?php foreach ($semesters as $optionSemester): ?>
                            <option
                                value="<?= e($optionSemester) ?>"
                                <?= $semester === $optionSemester ? 'selected' : '' ?>
                            >
                                <?= e($optionSemester) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label class="form-label" for="result-department">
                        Department
                    </label>

                    <select
                        class="select"
                        id="result-department"
                        name="department"
                        required
                    >
                        <option value="">Select</option>

                        <?php foreach ($departments as $optionDepartment): ?>
                            <option
                                value="<?= e($optionDepartment) ?>"
                                <?= $department === $optionDepartment ? 'selected' : '' ?>
                            >
                                <?= e($optionDepartment) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <button class="btn btn-primary" type="submit">
                Open result editor
            </button>

        </form>

    </section>

    <?php if ($selected): ?>

        <section class="card card-pad results-context-card">

            <div class="toolbar results-context-toolbar">

                <div>

                    <h2 class="section-heading">
                        <?= e($year) ?>
                        ·
                        <?= e($batch) ?>
                        ·
                        <?= e($semester) ?>
                        ·
                        <?= e($department) ?>
                    </h2>

                    <p class="section-copy results-count-copy">
                        <?= e((string) count($rows)) ?>
                        result rows currently stored.
                    </p>

                </div>

                <div class="action-row">

                    <?php if ($publication && (int) $publication['is_live'] === 1): ?>

                        <span class="badge badge-live">
                            Published
                        </span>

                        <form
                            method="post"
                            action="<?= e(dr_url('actions/result-publish.php')) ?>"
                            class="inline-form"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="<?= e(csrf_token()) ?>"
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
                                value="<?= e($semester) ?>"
                            >

                            <input
                                type="hidden"
                                name="department"
                                value="<?= e($department) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="unpublish"
                            >

                            <button
                                class="btn btn-secondary btn-sm"
                                type="submit"
                                data-confirm="Unpublish this result context from student view?"
                            >
                                Unpublish
                            </button>
                        </form>

                    <?php else: ?>

                        <span class="badge badge-off">
                            Not published
                        </span>

                        <form
                            method="post"
                            action="<?= e(dr_url('actions/result-publish.php')) ?>"
                            class="inline-form"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="<?= e(csrf_token()) ?>"
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
                                value="<?= e($semester) ?>"
                            >

                            <input
                                type="hidden"
                                name="department"
                                value="<?= e($department) ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="publish"
                            >

                            <button
                                class="btn btn-primary btn-sm"
                                type="submit"
                                data-confirm="Publish this result context to verified students?"
                            >
                                Publish results
                            </button>
                        </form>

                    <?php endif; ?>

                </div>

            </div>

            <div class="action-row">

                <a
                    class="btn btn-secondary btn-sm"
                    href="<?= e(
                        dr_url('admin/results/editor.php?' . $contextQuery)
                    ) ?>"
                >
                    Open full editor
                </a>

                <a
                    class="btn btn-secondary btn-sm"
                    href="<?= e(
                        dr_url('admin/results/view.php?' . $contextQuery)
                    ) ?>"
                >
                    Preview results
                </a>

            </div>

        </section>

    <?php endif; ?>

</main>

    </div>
</div>

<?php
page_end();
