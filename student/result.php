<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/student-queries.php';

require_student();

$publicationId = safe_int(
    $_GET['publication'] ?? null
);

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

$publication = get_publication_for_student(
    (int) $student['id'],
    $publicationId
);

if (!$publication) {
    flash(
        'error',
        'That published result could not be found.'
    );

    redirect('student/dashboard.php');
}

$rows = get_results_for_context(
    (string) $student['ien_number'],
    (string) $publication['year'],
    (string) $publication['batch'],
    (string) $publication['semester'],
    (string) $publication['department']
);

$total = 0;

foreach ($rows as $row) {
    $total +=
        (int) ($row['TW_marks'] ?? 0)
        + (int) ($row['PR_marks'] ?? 0)
        + (int) ($row['ESE_marks'] ?? 0);
}

page_start('Result', 'student');

student_nav();

render_flashes();
?>

<main class="student-shell container">
    <div class="result-title">
        <div>
            <span class="eyebrow">
                PUBLISHED RESULT
            </span>

            <h1 class="page-title">
                <?= e($publication['semester']) ?>
                ·
                <?= e($publication['year']) ?>
            </h1>

            <p class="page-subtitle">
                <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                · IEN
                <?= e($student['ien_number']) ?>
                ·
                <?= e($publication['department']) ?>
            </p>
        </div>

        <div class="result-total">
            <strong><?= e($total) ?></strong>
            <span>Total marks recorded</span>
        </div>
    </div>

    <section class="card card-pad">
        <div class="result-summary result-summary-spaced">
            <span class="summary-chip">
                Batch:
                <strong><?= e($publication['batch']) ?></strong>
            </span>

            <span class="summary-chip">
                Semester:
                <strong><?= e($publication['semester']) ?></strong>
            </span>

            <span class="summary-chip">
                Department:
                <strong><?= e($publication['department']) ?></strong>
            </span>
        </div>

        <?php if (!$rows): ?>

            <div class="empty">
                No marks have been entered for this published context.
            </div>

        <?php else: ?>

            <div class="table-wrap">
                <table class="table">
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
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $subtotal =
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
                                    <?= $row['TW_marks'] === null
                                        ? '—'
                                        : e($row['TW_marks'])
                                    ?>
                                </td>

                                <td>
                                    <?= $row['PR_marks'] === null
                                        ? '—'
                                        : e($row['PR_marks'])
                                    ?>
                                </td>

                                <td>
                                    <?= $row['ESE_marks'] === null
                                        ? '—'
                                        : e($row['ESE_marks'])
                                    ?>
                                </td>

                                <td>
                                    <strong><?= e($subtotal) ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

        <div class="certificate-actions certificate-actions-spaced">
            <a
                class="btn btn-primary"
                href="<?= e(
                    dr_url(
                        'certificate.php?publication='
                        . (int) $publication['id']
                    )
                ) ?>"
            >
                Open Certificate
            </a>

            <a
                class="btn btn-secondary"
                href="<?= e(dr_url('student/dashboard.php')) ?>"
            >
                Back to dashboard
            </a>
        </div>
    </section>
</main>

<?php page_end(); ?>
