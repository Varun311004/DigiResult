<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/admin-queries.php';

require_admin();

$conn = db();

$stats = [
    'students' => (int) (
        $conn
            ->query('SELECT COUNT(*) AS c FROM students')
            ->fetch_assoc()['c'] ?? 0
    ),
    'results' => (int) (
        $conn
            ->query('SELECT COUNT(*) AS c FROM results')
            ->fetch_assoc()['c'] ?? 0
    ),
    'published' => (int) (
        $conn
            ->query(
                'SELECT COUNT(*) AS c
                 FROM result_publications
                 WHERE is_live = 1'
            )
            ->fetch_assoc()['c'] ?? 0
    ),
    'subjects' => (int) (
        $conn
            ->query('SELECT COUNT(*) AS c FROM subjects')
            ->fetch_assoc()['c'] ?? 0
    ),
];

$recent = $conn
    ->query(
        'SELECT id, action, details, created_at
         FROM audit_logs
         ORDER BY id DESC
         LIMIT 8'
    )
    ->fetch_all(MYSQLI_ASSOC);

page_start(
    'Admin Dashboard',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('dashboard'); ?>

    <div class="admin-main">
        <?php admin_topbar('Dashboard'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <div class="grid grid-4 dashboard-toolbar">
                <?php
                $metrics = [
                    ['Students', $stats['students']],
                    ['Result rows', $stats['results']],
                    ['Published contexts', $stats['published']],
                    ['Subjects', $stats['subjects']],
                ];
                ?>

                <?php foreach ($metrics as [$label, $value]): ?>
                    <section class="card metric">
                        <div class="label">
                            <?= e($label) ?>
                        </div>

                        <div class="value">
                            <?= e($value) ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-2">
                <section class="card card-pad">
                    <h2 class="section-heading">
                        Quick actions
                    </h2>

                    <p class="section-copy">
                        Common administration tasks.
                    </p>

                    <div class="action-row">
                        <a
                            class="btn btn-primary"
                            href="<?= e(
                                dr_url('admin/students/create.php')
                            ) ?>"
                        >
                            Add student
                        </a>

                        <a
                            class="btn btn-secondary"
                            href="<?= e(
                                dr_url('admin/results/index.php')
                            ) ?>"
                        >
                            Manage results
                        </a>

                        <a
                            class="btn btn-secondary"
                            href="<?= e(
                                dr_url('admin/academic/index.php')
                            ) ?>"
                        >
                            Academic setup
                        </a>
                    </div>
                </section>

                <section class="card card-pad">
                    <h2 class="section-heading">
                        Recent admin activity
                    </h2>

                    <p class="section-copy">
                        Latest actions recorded by DigiResult.
                    </p>

                    <?php if (!$recent): ?>
                        <div class="empty">
                            No audit entries yet.
                        </div>
                    <?php else: ?>
                        <div class="grid">
                            <?php foreach ($recent as $entry): ?>
                                <div>
                                    <strong>
                                        <?= e($entry['action']) ?>
                                    </strong>

                                    <div class="muted dashboard-detail">
                                        <?= e($entry['details'] ?? '') ?>
                                        ·
                                        <?= e(
                                            date(
                                                'd M Y H:i',
                                                strtotime(
                                                    $entry['created_at']
                                                )
                                            )
                                        ) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</div>

<?php page_end(); ?>
