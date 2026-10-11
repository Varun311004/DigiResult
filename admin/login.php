<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

page_start('Admin Login');
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
                    Administrator sign in
                </h1>

                <p
                    class="page-subtitle auth-subtitle"
                >
                    Use the administrator account configured in the DigiResult database.
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
                    action="<?= e(dr_url('actions/admin-login.php')) ?>"
                    class="auth-section-spaced"
                >

                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <div class="field">

                        <label class="form-label required">
                            Admin Email
                        </label>

                        <input
                            class="input"
                            name="email"
                            type="email"
                            required
                            autocomplete="username"
                        >

                    </div>

                    <div class="field">

                        <label class="form-label required">
                            Password
                        </label>

                        <input
                            class="input"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                        >

                    </div>

                    <button
                        class="btn btn-primary btn-block"
                        type="submit"
                    >
                        Sign in
                    </button>

                </form>

                <p class="auth-link-spaced">

                    <a
                        class="admin-link"
                        href="<?= e(dr_url('admin/forgot-password.php')) ?>"
                    >
                        Forgot password?
                    </a>

                </p>

            </section>

        </div>

    </main>

</div>

<?php page_end(); ?>
