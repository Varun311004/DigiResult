<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
}

page_start('Forgot Admin Password');
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
                    Reset administrator password
                </h1>

                <p
                    class="page-subtitle auth-subtitle"
                >
                    Enter the email address associated with your DigiResult administrator account.
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
                    action="<?= e(dr_url('actions/admin-forgot-password.php')) ?>"
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
                            autocomplete="email"
                            maxlength="255"
                            autofocus
                        >

                    </div>

                    <button
                        class="btn btn-primary btn-block"
                        type="submit"
                    >
                        Send reset link
                    </button>

                </form>

                <p
                    class="help auth-link-spaced-small"
                >
                    The reset link expires automatically and can be used only once.
                </p>

                <p class="auth-link-spaced">

                    <a
                        class="admin-link"
                        href="<?= e(dr_url('admin/login.php')) ?>"
                    >
                        Back to administrator sign in
                    </a>

                </p>

            </section>

        </div>

    </main>

</div>

<?php page_end(); ?>
