<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/student-queries.php';

require_student();

$student = get_student(
    (int) $_SESSION['student_id']
);

if (!$student) {
    logout_all();

    flash(
        'error',
        'Your student record is no longer available. Please contact the institute.'
    );

    redirect('index.php');
}

$contexts = get_published_contexts_for_student(
    (string) $student['ien_number']
);

page_start(
    'Student Dashboard',
    'student'
);

student_nav();

render_flashes();
?>

<main class="student-shell container">
    <div class="student-hero">
        <div>
            <span class="eyebrow">
                STUDENT DASHBOARD
            </span>

            <h1 class="page-title">
                Welcome, <?= e($student['first_name']) ?>.
            </h1>

            <p class="page-subtitle">
                Your verified academic information and published
                results are shown here.
            </p>
        </div>
    </div>

    <div class="grid grid-2">
        <section class="card profile-card">
            <h2 class="section-heading">
                Student information
            </h2>

            <p class="section-copy">
                The identity details linked to your IEN.
            </p>

            <div class="profile-grid">
                <div class="profile-item">
                    <span>Full name</span>
                    <strong>
                        <?= e(
                            trim(
                                $student['first_name']
                                . ' '
                                . $student['last_name']
                            )
                        ) ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <span>IEN number</span>
                    <strong><?= e($student['ien_number']) ?></strong>
                </div>

                <div class="profile-item">
                    <span>Student number</span>
                    <strong><?= e($student['student_number']) ?></strong>
                </div>

                <div class="profile-item">
                    <span>Email</span>
                    <strong><?= e($student['email']) ?></strong>
                </div>

                <div class="profile-item">
                    <span>Department</span>
                    <strong><?= e($student['department']) ?></strong>
                </div>

                <div class="profile-item">
                    <span>Batch / Semester</span>
                    <strong>
                        <?= e($student['batch']) ?>
                        ·
                        <?= e($student['semester']) ?>
                    </strong>
                </div>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="section-heading">
                Published results
            </h2>

            <p class="section-copy">
                Only result groups officially marked live by an administrator appear here.
            </p>

            <?php if (!$contexts): ?>

                <div class="empty">
                    No published result is available for this student yet.
                </div>

            <?php else: ?>

                <div class="grid">
                    <?php foreach ($contexts as $context): ?>
                        <div class="context-card">
                            <div>
                                <strong>
                                    <?= e($context['semester']) ?>
                                    ·
                                    <?= e($context['year']) ?>
                                </strong>

                                <div class="context-preview">
                                    <?= e($context['department']) ?>
                                    ·
                                    <?= e($context['batch']) ?>
                                    <br>
                                    Published
                                    <?= e(
                                        date(
                                            'd M Y, h:i A',
                                            strtotime(
                                                (string) $context['published_at']
                                            )
                                        )
                                    ) ?>
                                </div>
                            </div>

                            <div class="action-row">
                                <a
                                    class="btn btn-secondary btn-sm"
                                    href="<?= e(
                                        dr_url(
                                            'student/result.php?publication='
                                            . (int) $context['id']
                                        )
                                    ) ?>"
                                >
                                    View result
                                </a>

                                <a
                                    class="btn btn-primary btn-sm"
                                    href="<?= e(
                                        dr_url(
                                            'certificate.php?publication='
                                            . (int) $context['id']
                                        )
                                    ) ?>"
                                >
                                    Certificate
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </section>
    </div>
</main>

<?php page_end(); ?>
