<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

$years = lookup_rows(
    'year',
    'Year',
    'Year'
);

$batches = lookup_rows(
    'batch',
    'Batch',
    'Batch'
);

$semesters = lookup_rows(
    'semester',
    'Semester',
    'Semester'
);

$departments = lookup_rows(
    'department',
    'Department',
    'Department'
);

$subjectFilter = get_string(
    'subject',
    150
);

$subjectBatch = get_string(
    'batch',
    50
);

$subjectSemester = get_string(
    'semester',
    50
);

$subjectDepartment = get_string(
    'department',
    100
);

$subjectSql = <<<'SQL'
SELECT *
FROM subjects
WHERE (? = '' OR Subject LIKE ?)
  AND (? = '' OR Batch = ?)
  AND (? = '' OR Semester = ?)
  AND (? = '' OR Department = ?)
ORDER BY Batch, Semester, Department, Subject
SQL;

$subjectStmt = db()->prepare(
    $subjectSql
);

$subjectLike = '%' . $subjectFilter . '%';

$subjectStmt->bind_param(
    'ssssssss',
    $subjectFilter,
    $subjectLike,
    $subjectBatch,
    $subjectBatch,
    $subjectSemester,
    $subjectSemester,
    $subjectDepartment,
    $subjectDepartment
);

$subjectStmt->execute();

$subjects = $subjectStmt
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);

$subjectStmt->close();

page_start(
    'Academic Setup',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('academic'); ?>

    <div class="admin-main">
        <?php admin_topbar('Academic Setup'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <div class="grid academic-sections">
                <section class="card card-pad">
                    <span class="eyebrow">
                        ACADEMIC LOOKUPS
                    </span>

                    <h2 class="section-heading academic-heading">
                        Years, batches, semesters, departments
                    </h2>

                    <p class="section-copy">
                        These values feed student and result forms.
                    </p>

                    <div class="grid grid-2">
                        <?php
                        $lookupGroups = [
                            [
                                'year',
                                'Year',
                                $years,
                            ],
                            [
                                'batch',
                                'Batch',
                                $batches,
                            ],
                            [
                                'semester',
                                'Semester',
                                $semesters,
                            ],
                            [
                                'department',
                                'Department',
                                $departments,
                            ],
                        ];
                        ?>

                        <?php foreach ($lookupGroups as [$table, $label, $values]): ?>
                            <div class="card card-pad lookup-card">
                                <strong><?= e($label) ?></strong>

                                <form
                                    method="post"
                                    action="<?= e(
                                        dr_url('actions/academic-save.php')
                                    ) ?>"
                                    class="lookup-form-spaced"
                                >
                                    <input
                                        type="hidden"
                                        name="_csrf"
                                        value="<?= e(csrf_token()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="type"
                                        value="<?= e($table) ?>"
                                    >

                                    <div class="action-row">
                                        <input
                                            class="input"
                                            name="value"
                                            required
                                            maxlength="100"
                                            placeholder="Add <?= e(
                                                strtolower($label)
                                            ) ?>"
                                        >

                                        <button class="btn btn-primary btn-sm">
                                            Add
                                        </button>
                                    </div>
                                </form>

                                <div class="action-row lookup-values">
                                    <?php foreach ($values as $value): ?>
                                        <span class="badge badge-off lookup-badge">
                                            <?= e($value['label']) ?>

                                            <form
                                                method="post"
                                                action="<?= e(
                                                    dr_url(
                                                        'actions/academic-delete.php'
                                                    )
                                                ) ?>"
                                                class="inline-form"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="_csrf"
                                                    value="<?= e(
                                                        csrf_token()
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="type"
                                                    value="<?= e($table) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= e($value['id']) ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="icon-button"
                                                    aria-label="Delete"
                                                    data-confirm="Delete this <?= e(
                                                        strtolower($label)
                                                    ) ?> if it is not in use?"
                                                >
                                                    ×
                                                </button>
                                            </form>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="card card-pad subject-management-card">
                    <span class="eyebrow">
                        SUBJECTS
                    </span>

                    <h2 class="section-heading academic-heading">
                        Subject configuration
                    </h2>

                    <p class="section-copy">
                        Choose the assessment components available for each subject.
                    </p>

                    <form
                        method="post"
                        action="<?= e(
                            dr_url('actions/subject-save.php')
                        ) ?>"
                    >
                        <input
                            type="hidden"
                            name="_csrf"
                            value="<?= e(csrf_token()) ?>"
                        >

                        <div class="field">
                            <label class="form-label required">
                                Subject
                            </label>

                            <input
                                class="input"
                                name="subject"
                                required
                                maxlength="150"
                                placeholder="e.g. Computer Network"
                            >
                        </div>

                        <div class="grid grid-3">
                            <div class="field">
                                <label class="form-label component-option">
                                    <input
                                        type="checkbox"
                                        name="tw"
                                        value="1"
                                    >
                                    <span>TW</span>
                                </label>
                            </div>

                            <div class="field">
                                <label class="form-label component-option">
                                    <input
                                        type="checkbox"
                                        name="pr"
                                        value="1"
                                    >
                                    <span>PR</span>
                                </label>
                            </div>

                            <div class="field">
                                <label class="form-label component-option">
                                    <input
                                        type="checkbox"
                                        name="ese"
                                        value="1"
                                    >
                                    <span>ESE</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-3">
                            <div class="field">
                                <label class="form-label">
                                    Batch

                                    <select
                                        class="select"
                                        name="batch"
                                        required
                                    >
                                        <option value="">
                                            Select batch
                                        </option>

                                        <?php foreach ($batches as $value): ?>
                                            <option
                                                value="<?= e($value['label']) ?>"
                                            >
                                                <?= e($value['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="field">
                                <label class="form-label">
                                    Semester

                                    <select
                                        class="select"
                                        name="semester"
                                        required
                                    >
                                        <option value="">
                                            Select semester
                                        </option>

                                        <?php foreach ($semesters as $value): ?>
                                            <option
                                                value="<?= e($value['label']) ?>"
                                            >
                                                <?= e($value['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>

                            <div class="field">
                                <label class="form-label">
                                    Department

                                    <select
                                        class="select"
                                        name="department"
                                        required
                                    >
                                        <option value="">
                                            Select department
                                        </option>

                                        <?php foreach ($departments as $value): ?>
                                            <option
                                                value="<?= e($value['label']) ?>"
                                            >
                                                <?= e($value['label']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-primary">
                            Add subject
                        </button>
                    </form>

                    <div class="subject-filter-panel">
                        <div class="subject-filter-heading">
                            <div>
                                <strong>Filter subjects</strong>
                                <span>
                                    Quickly narrow the configured subject list.
                                </span>
                            </div>

                            <a
                                class="btn btn-secondary btn-sm"
                                href="<?= e(
                                    dr_url(
                                        'admin/academic/index.php'
                                    )
                                ) ?>"
                            >
                                Clear filters
                            </a>
                        </div>

                        <form
                            method="get"
                            class="subject-filters"
                        >
                            <div class="field">
                                <label class="form-label">
                                    Subject
                                </label>

                                <input
                                    class="input"
                                    name="subject"
                                    value="<?= e($subjectFilter) ?>"
                                    placeholder="Search subject"
                                >
                            </div>

                            <div class="field">
                                <label class="form-label">
                                    Batch
                                </label>

                                <select
                                    class="select"
                                    name="batch"
                                >
                                    <option value="">
                                        All batches
                                    </option>

                                    <?php foreach ($batches as $value): ?>
                                        <option
                                            value="<?= e($value['label']) ?>"
                                            <?= $subjectBatch === $value['label']
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            <?= e($value['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label class="form-label">
                                    Semester
                                </label>

                                <select
                                    class="select"
                                    name="semester"
                                >
                                    <option value="">
                                        All semesters
                                    </option>

                                    <?php foreach ($semesters as $value): ?>
                                        <option
                                            value="<?= e($value['label']) ?>"
                                            <?= $subjectSemester === $value['label']
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            <?= e($value['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label class="form-label">
                                    Department
                                </label>

                                <select
                                    class="select"
                                    name="department"
                                >
                                    <option value="">
                                        All departments
                                    </option>

                                    <?php foreach ($departments as $value): ?>
                                        <option
                                            value="<?= e($value['label']) ?>"
                                            <?= $subjectDepartment === $value['label']
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            <?= e($value['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="subject-filter-action">
                                <button class="btn btn-secondary">
                                    Apply filters
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php
                    $filteredSubjectCount = count($subjects);
                    $hasSubjectFilters =
                        $subjectFilter !== ''
                        || $subjectBatch !== ''
                        || $subjectSemester !== ''
                        || $subjectDepartment !== '';
                    ?>

                    <div class="subject-list-meta">
                        <strong><?= e($filteredSubjectCount) ?></strong>
                        subject configuration<?= $filteredSubjectCount === 1 ? '' : 's' ?>
                        shown
                        <?php if ($hasSubjectFilters): ?>
                            with active filters
                        <?php endif; ?>
                    </div>

                    <div class="table-wrap lookup-table">
                        <table class="table subject-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Context</th>
                                    <th>TW</th>
                                    <th>PR</th>
                                    <th>ESE</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!$subjects): ?>
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="empty"
                                        >
                                            No subject configurations match these filters.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?= e($subject['Subject']) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= e(
                                                    $subject['Batch']
                                                    . ' · '
                                                    . $subject['Semester']
                                                    . ' · '
                                                    . $subject['Department']
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= (string) $subject['tw_checked'] === '1'
                                                    ? 'Yes'
                                                    : '—'
                                                ?>
                                            </td>

                                            <td>
                                                <?= (string) $subject['pr_checked'] === '1'
                                                    ? 'Yes'
                                                    : '—'
                                                ?>
                                            </td>

                                            <td>
                                                <?= (string) $subject['ese_checked'] === '1'
                                                    ? 'Yes'
                                                    : '—'
                                                ?>
                                            </td>

                                            <td>
                                                <form
                                                    method="post"
                                                    action="<?= e(
                                                        dr_url(
                                                            'actions/subject-delete.php'
                                                        )
                                                    ) ?>"
                                                    class="inline-form"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="_csrf"
                                                        value="<?= e(
                                                            csrf_token()
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= e(
                                                            $subject['id']
                                                        ) ?>"
                                                    >

                                                    <button
                                                        class="btn btn-danger btn-sm"
                                                        data-confirm="Delete this subject configuration?"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</div>

<?php page_end(); ?>
