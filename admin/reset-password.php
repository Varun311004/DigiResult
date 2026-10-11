<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

$token = trim(
    (string) ($_GET['token'] ?? '')
);

$tokenHash = $token !== ''
    ? hash('sha256', $token)
    : '';

$reset = null;

if ($tokenHash !== '') {
    $stmt = db()->prepare(<<<'SQL'
        SELECT id, email, expires_at, used_at
        FROM admin_password_resets
        WHERE token_hash = ?
          AND used_at IS NULL
          AND expires_at > NOW()
        LIMIT 1
        SQL
    );

    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();

    $reset = $stmt->get_result()->fetch_assoc() ?: null;

    $stmt->close();
}

$isValid = $reset !== null;

if (!$isValid) {
    flash(
        'error',
        'This password reset link is invalid or has expired. Please request a new one.'
    );

    redirect('admin/forgot-password.php');
}

page_start('Reset Admin Password');
public_nav();

$flashes = consume_flash();
?>

<div class="auth-page">

    <main class="auth-layout">

        <div
            class="auth-shell card auth-shell-single"
        >

            <section class="auth-form">

                <span class="eyebrow">
                    ADMINISTRATION
                </span>

                <h1
                    class="page-title auth-title"
                >
                    Create a new password
                </h1>

                <p
                    class="page-subtitle auth-subtitle"
                >
                    Choose a new password for the administrator account.
                </p>

                <?php foreach ($flashes as $item): ?>

                    <div
                        class="flash flash-<?= e($item['type']) ?> auth-flash"
                    >
                        <span>
                            <?= e($item['message']) ?>
                        </span>
                    </div>

                <?php endforeach; ?>

                <form
                    method="post"
                    action="<?= e(dr_url('actions/admin-reset-password.php')) ?>"
                    class="auth-section-spaced"
                >

                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <input
                        type="hidden"
                        name="token"
                        value="<?= e($token) ?>"
                    >

                    <div class="field">

                        <label class="form-label required">
                            New Password
                        </label>

                        <input
                            class="input"
                            name="new_password"
                            type="password"
                            minlength="10"
                            required
                            autocomplete="new-password"
                        >

                    </div>

                    <div class="field">

                        <label class="form-label required">
                            Confirm New Password
                        </label>

                        <input
                            class="input"
                            name="confirm_password"
                            type="password"
                            minlength="10"
                            required
                            autocomplete="new-password"
                        >

                    </div>

                    <button
                        class="btn btn-primary btn-block"
                        type="submit"
                    >
                        Update password
                    </button>

                </form>

            </section>

        </div>

    </main>

</div>

<?php page_end(); ?>
