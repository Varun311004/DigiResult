<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

$checks = [];

try {
    $connection = db();

    $checks[] = [
        'Database connection',
        true,
        'MySQL/MariaDB connection successful',
    ];

    $requiredTables = [
        'students',
        'results',
        'subjects',
        'batch',
        'semester',
        'department',
        'year',
        'admincreds',
        'student_otps',
        'result_publications',
        'audit_logs',
    ];

    foreach ($requiredTables as $table) {
        $stmt = $connection->prepare(
            'SHOW TABLES LIKE ?'
        );

        $stmt->bind_param(
            's',
            $table
        );

        $stmt->execute();

        $exists = $stmt
            ->get_result()
            ->num_rows > 0;

        $checks[] = [
            'Table: ' . $table,
            $exists,
            $exists ? 'Present' : 'Missing',
        ];

        $stmt->close();
    }
} catch (Throwable $e) {
    $checks[] = [
        'Database connection',
        false,
        $e->getMessage(),
    ];
}

$checks[] = [
    'PHP version',
    version_compare(PHP_VERSION, '8.0.0', '>='),
    'Running ' . PHP_VERSION,
];

$checks[] = [
    'Randomness API',
    function_exists('random_int'),
    'random_int available',
];

$checks[] = [
    'Password hashing',
    function_exists('password_hash'),
    'password_hash available',
];

$checks[] = [
    'PHP mail()',
    function_exists('mail'),
    'mail() function '
    . (function_exists('mail') ? 'available' : 'unavailable'),
];

$checks[] = [
    'cURL / HTTPS API',
    function_exists('curl_init')
        || (bool) ini_get('allow_url_fopen'),
    'HTTPS email API can use '
    . (function_exists('curl_init') ? 'cURL' : 'PHP stream fallback'),
];

$checks[] = [
    'Application URL',
    true,
    dr_url('certificate.php') . ' (web URL)',
];

$mailMode = strtolower(
    (string) cfg('mail.mode', 'native')
);

$checks[] = [
    'Brevo API key configured',
    $mailMode !== 'brevo_api'
        || trim(
            (string) cfg('mail.brevo_api_key', '')
        ) !== '',
    $mailMode === 'brevo_api'
        ? 'A Brevo API key is configured'
        : 'Brevo API is not the selected transport',
];

$checks[] = [
    'Brevo sender configured',
    $mailMode !== 'brevo_api'
        || filter_var(
            (string) cfg('mail.from_email', ''),
            FILTER_VALIDATE_EMAIL
        ) !== false,
    $mailMode === 'brevo_api'
        ? 'Sender: '
            . (string) cfg(
                'mail.from_email',
                '(missing)'
            )
        : 'Brevo is not the selected transport',
];

$checks[] = [
    'Brevo timeout',
    $mailMode !== 'brevo_api'
        || (int) cfg(
            'mail.brevo_timeout_seconds',
            15
        ) >= 5,
    'Timeout: '
    . (string) cfg(
        'mail.brevo_timeout_seconds',
        15
    )
    . 's',
];

page_start(
    'System Health',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('health'); ?>

    <div class="admin-main">
        <?php admin_topbar('System Health'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <section class="card card-pad">
                <span class="eyebrow">
                    EMAIL TEST
                </span>

                <h2 class="section-heading health-heading">
                    Test transactional email
                </h2>

                <p class="section-copy">
                    Use this on XAMPP and after deployment to verify the
                    Brevo HTTPS API independently of the student OTP flow.
                </p>

                <form
                    method="post"
                    action="<?= e(
                        dr_url('actions/send-test-email.php')
                    ) ?>"
                    class="action-row health-form"
                >
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <div class="field health-recipient-field">
                        <label
                            class="form-label"
                            for="test-email"
                        >
                            Test recipient
                        </label>

                        <input
                            class="input"
                            id="test-email"
                            name="email"
                            type="email"
                            value="<?= e(
                                (string) cfg(
                                    'mail.test_recipient',
                                    ''
                                ) ?: (string) (
                                    $_SESSION['admin_user'] ?? ''
                                )
                            ) ?>"
                            required
                        >
                    </div>

                    <button class="btn btn-primary">
                        Send test email
                    </button>
                </form>

                <section class="card card-pad health-card">
                    <span class="eyebrow">
                        DEPLOYMENT CHECK
                    </span>

                    <h2 class="section-heading health-heading">
                        Runtime readiness
                    </h2>

                    <p class="section-copy">
                        This page checks the pieces DigiResult expects
                        on XAMPP or ByetHost.
                    </p>

                    <div class="health-grid">
                        <?php foreach ($checks as $check): ?>
                            <div class="health-item">
                                <div>
                                    <strong>
                                        <?= e($check[0]) ?>
                                    </strong>

                                    <div class="muted health-detail">
                                        <?= e($check[2]) ?>
                                    </div>
                                </div>

                                <span class="<?= $check[1] ? 'ok' : 'bad' ?>">
                                    <?= $check[1] ? 'OK' : 'CHECK' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </section>
        </main>
    </div>
</div>

<?php page_end(); ?>
