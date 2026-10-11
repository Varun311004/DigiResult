<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

if (!isset($student)) {
    $student = null;
}

$years = lookup_rows('year', 'Year', 'Year');
$batches = lookup_rows('batch', 'Batch', 'Batch');
$semesters = lookup_rows('semester', 'Semester', 'Semester');
$departments = lookup_rows('department', 'Department', 'Department');

page_start(
    'Add Student',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('students'); ?>

    <div class="admin-main">
        <?php admin_topbar('Add Student'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <section class="card card-pad">
                <span class="eyebrow">
                    STUDENT MANAGEMENT
                </span>

                <h2 class="section-heading student-form-heading">
                    Create student record
                </h2>

                <p class="section-copy">
                    The email saved here becomes the address used for OTP verification.
                </p>

<form
    method="post"
    action="<?= e(dr_url('actions/student-save.php')) ?>"
>
    <input
        type="hidden"
        name="_csrf"
        value="<?= e(csrf_token()) ?>"
    >

    <input
        type="hidden"
        name="id"
        value="<?= e($student['id'] ?? '') ?>"
    >

    <div class="grid grid-2">
        <div class="field">
            <label class="form-label required">
                IEN Number
            </label>

            <input
                class="input"
                name="ien_number"
                value="<?= e($student['ien_number'] ?? '') ?>"
                maxlength="20"
                inputmode="numeric"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                Student Number
            </label>

            <input
                class="input"
                name="student_number"
                value="<?= e($student['student_number'] ?? '') ?>"
                maxlength="20"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                First Name
            </label>

            <input
                class="input"
                name="first_name"
                value="<?= e($student['first_name'] ?? '') ?>"
                maxlength="100"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                Last Name
            </label>

            <input
                class="input"
                name="last_name"
                value="<?= e($student['last_name'] ?? '') ?>"
                maxlength="100"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                Date of Birth
            </label>

            <input
                class="input"
                name="date_of_birth"
                type="date"
                value="<?= e($student['date_of_birth'] ?? '') ?>"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                Gender
            </label>

            <select
                class="select"
                name="gender"
                required
            >
                <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                    <option
                        value="<?= e($gender) ?>"
                        <?= ($student['gender'] ?? '') === $gender ? 'selected' : '' ?>
                    >
                        <?= e($gender) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label class="form-label required">
                Email
            </label>

            <input
                class="input"
                name="email"
                type="email"
                value="<?= e($student['email'] ?? '') ?>"
                maxlength="255"
                required
            >
        </div>

        <div class="field">
            <label class="form-label required">
                Year
            </label>

            <select
                class="select"
                name="year"
                required
            >
                <?php foreach ($years as $yearOption): ?>
                    <option
                        value="<?= e($yearOption['label']) ?>"
                        <?= (string) ($student['year'] ?? '')
                            === (string) $yearOption['label']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($yearOption['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label class="form-label required">
                Batch
            </label>

            <select
                class="select"
                name="batch"
                required
            >
                <?php foreach ($batches as $batchOption): ?>
                    <option
                        value="<?= e($batchOption['label']) ?>"
                        <?= ($student['batch'] ?? '')
                            === $batchOption['label']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($batchOption['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label class="form-label required">
                Semester
            </label>

            <select
                class="select"
                name="semester"
                required
            >
                <?php foreach ($semesters as $semesterOption): ?>
                    <option
                        value="<?= e($semesterOption['label']) ?>"
                        <?= ($student['semester'] ?? '')
                            === $semesterOption['label']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($semesterOption['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label class="form-label required">
                Department
            </label>

            <select
                class="select"
                name="department"
                required
            >
                <?php foreach ($departments as $departmentOption): ?>
                    <option
                        value="<?= e($departmentOption['label']) ?>"
                        <?= ($student['department'] ?? '')
                            === $departmentOption['label']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($departmentOption['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field field-full">
            <label class="form-label required">
                Address
            </label>

            <textarea
                class="textarea"
                name="address"
                rows="3"
                maxlength="255"
                required
            ><?= e($student['address'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="action-row">
        <button class="btn btn-primary">
            <?= isset($student['id'])
                ? 'Update student'
                : 'Create student'
            ?>
        </button>

        <a
            class="btn btn-secondary"
            href="<?= e(
                dr_url('admin/students/index.php')
            ) ?>"
        >
            Cancel
        </a>
    </div>
</form>

            </section>
        </main>
    </div>
</div>

<?php page_end(); ?>
