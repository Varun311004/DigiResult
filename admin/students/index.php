<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/layout.php';
require_once __DIR__ . '/../../includes/admin-queries.php';

require_admin();

$search = get_string('q', 100);
$rows = list_students($search);

page_start(
    'Students',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('students'); ?>

    <div class="admin-main">
        <?php admin_topbar('Students'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <div class="toolbar">
                <div>
                    <span class="eyebrow">
                        STUDENT MANAGEMENT
                    </span>

                    <p class="section-copy student-filter-copy">
                        Search, add and maintain student records.
                    </p>
                </div>

                <a
                    class="btn btn-primary"
                    href="<?= e(
                        dr_url('admin/students/create.php')
                    ) ?>"
                >
                    Add student
                </a>
            </div>

            <form
                class="card filters"
                method="get"
            >
                <div class="action-row">
                    <input
                        class="input student-search-field"
                        name="q"
                        value="<?= e($search) ?>"
                        placeholder="Search IEN, name, email or student number"
                    >

                    <button class="btn btn-secondary">
                        Search
                    </button>

                    <?php if ($search !== ''): ?>
                        <a
                            class="btn btn-secondary"
                            href="<?= e(
                                dr_url('admin/students/index.php')
                            ) ?>"
                        >
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <section class="card student-table-card">
                <div class="table-wrap table-wrap-flat">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>IEN</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Academic context</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!$rows): ?>
                                <tr>
                                    <td
                                        colspan="6"
                                        class="empty"
                                    >
                                        No students found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $student): ?>
                                    <?php
                                    $active = (int) (
                                        $student['is_active'] ?? 1
                                    ) === 1;
                                    ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?= e($student['ien_number']) ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= e(
                                                $student['first_name']
                                                . ' '
                                                . $student['last_name']
                                            ) ?>

                                            <div class="muted student-table-small">
                                                <?= e(
                                                    $student['student_number']
                                                ) ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?= e($student['email']) ?>
                                        </td>

                                        <td>
                                            <?= e($student['year']) ?>
                                            ·
                                            <?= e($student['batch']) ?>
                                            ·
                                            <?= e($student['semester']) ?>
                                            ·
                                            <?= e($student['department']) ?>
                                        </td>

                                        <td>
                                            <?= $active
                                                ? '<span class="badge badge-live">Active</span>'
                                                : '<span class="badge badge-off">Deactivated</span>'
                                            ?>
                                        </td>

                                        <td>
                                            <div class="action-row">
                                                <a
                                                    class="btn btn-secondary btn-sm"
                                                    href="<?= e(
                                                        dr_url(
                                                            'admin/students/edit.php?id='
                                                            . (int) $student['id']
                                                        )
                                                    ) ?>"
                                                >
                                                    Edit
                                                </a>

                                                <form
                                                    method="post"
                                                    action="<?= e(
                                                        dr_url(
                                                            'actions/student-toggle.php'
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
                                                            $student['id']
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="status"
                                                        value="<?= $active
                                                            ? '0'
                                                            : '1'
                                                        ?>"
                                                    >

                                                    <button
                                                        class="btn btn-secondary btn-sm"
                                                        data-confirm="<?= $active
                                                            ? 'Deactivate this student? They will no longer be able to request OTP access.'
                                                            : 'Reactivate this student? They will be able to request OTP access again.'
                                                        ?>"
                                                    >
                                                        <?= $active
                                                            ? 'Deactivate'
                                                            : 'Activate'
                                                        ?>
                                                    </button>
                                                </form>

                                                <form
                                                    method="post"
                                                    action="<?= e(
                                                        dr_url(
                                                            'actions/student-delete.php'
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
                                                            $student['id']
                                                        ) ?>"
                                                    >

                                                    <button
                                                        class="btn btn-danger btn-sm"
                                                        data-confirm="Permanently delete this student? All linked result history will also be removed."
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<?php page_end(); ?>
