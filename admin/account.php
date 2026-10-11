<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

require_admin();

page_start(
    'Admin Account',
    'admin'
);
?>

<div class="admin-layout">
    <?php admin_sidebar('account'); ?>

    <div class="admin-main">
        <?php admin_topbar('Admin Account'); ?>

        <?php render_flashes(); ?>

        <main class="admin-content">
            <section class="card card-pad account-panel">
                <span class="eyebrow">
                    SECURITY
                </span>

                <h2 class="section-heading account-heading">
                    Change administrator password
                </h2>

                <p class="section-copy">
                    Passwords are stored as modern password hashes.
                    Existing legacy plaintext credentials are automatically
                    upgraded after a successful login.
                </p>

                <form
                    method="post"
                    action="<?= e(
                        dr_url('actions/admin-password.php')
                    ) ?>"
                >
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <div class="field">
                        <label class="form-label required">
                            Current password
                        </label>

                        <input
                            class="input"
                            type="password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <div class="field">
                        <label class="form-label required">
                            New password
                        </label>

                        <input
                            class="input"
                            type="password"
                            name="new_password"
                            minlength="10"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="field">
                        <label class="form-label required">
                            Confirm new password
                        </label>

                        <input
                            class="input"
                            type="password"
                            name="confirm_password"
                            minlength="10"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button class="btn btn-primary">
                        Update password
                    </button>
                </form>
            </section>
        </main>
    </div>
</div>

<?php page_end(); ?>
