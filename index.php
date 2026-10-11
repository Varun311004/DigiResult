<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (isset($_GET['reset'])) {
    unset(
        $_SESSION['otp_pending'],
        $_SESSION['otp_pending_ien'],
        $_SESSION['otp_pending_email'],
        $_SESSION['otp_pending_started'],
        $_SESSION['otp_resend_available_at'],
        $_SESSION['dev_otp_hint']
    );

    header(
        'Location: ' . dr_url('index.php')
    );

    exit;
}

page_start('Student Portal');

public_nav();

$flashes = consume_flash();
$pending = !empty($_SESSION['otp_pending']);
$otpResendAt = (int) (
    $_SESSION['otp_resend_available_at'] ?? 0
);
$otpResendRemaining = max(
    0,
    $otpResendAt - time()
);
?>

<div class="auth-page">
    <?php foreach ($flashes as $item): ?>
        <div class="flash flash-<?= e($item['type']) ?>">
            <span><?= e($item['message']) ?></span>
        </div>
    <?php endforeach; ?>

    <main class="auth-layout">
        <div class="auth-shell card">
            <section class="auth-visual">
                <div>
                    <span class="eyebrow auth-eyebrow">
                        NEW HORIZON EDUCATION SOCIETY
                    </span>

                    <h2>
                        Access your academic result securely.
                    </h2>

                    <p>
                        Verify your IEN and registered email, receive a
                        one-time password, and continue to your published
                        results and certificate-grade card.
                    </p>

                    <div class="auth-points">
                        <div class="auth-point">
                            <span class="auth-dot"></span>
                            <span>
                                OTP verification with expiry and attempt limits.
                            </span>
                        </div>

                        <div class="auth-point">
                            <span class="auth-dot"></span>
                            <span>
                                Results are available only after official publication.
                            </span>
                        </div>

                        <div class="auth-point">
                            <span class="auth-dot"></span>
                            <span>
                                Certificate-grade card can be printed or saved as PDF.
                            </span>
                        </div>
                    </div>
                </div>

                <div class="auth-address">
                    <?= e(cfg('app.address')) ?>
                </div>
            </section>

            <section class="auth-form">
                <span class="eyebrow">
                    STUDENT PORTAL
                </span>

                <h1 class="page-title auth-title">
                    <?= $pending ? 'Enter your OTP' : 'Verify your identity' ?>
                </h1>

                <p class="page-subtitle auth-subtitle">
                    <?=
                    $pending
                        ? 'A verification code has been sent to '
                            . e(
                                mask_email(
                                    (string) (
                                        $_SESSION['otp_pending_email'] ?? ''
                                    )
                                )
                            )
                            . '.'
                        : 'Use the IEN number and registered email stored by the institute.'
                    ?>
                </p>

                <?php if (!$pending): ?>

                    <form
                        method="post"
                        action="<?= e(dr_url('actions/send-otp.php')) ?>"
                        class="auth-form-spaced"
                    >
                        <input
                            type="hidden"
                            name="_csrf"
                            value="<?= e(csrf_token()) ?>"
                        >

                        <div class="field">
                            <label
                                class="form-label required"
                                for="ien"
                            >
                                IEN Number
                            </label>

                            <input
                                class="input"
                                id="ien"
                                name="ien"
                                inputmode="numeric"
                                maxlength="20"
                                autocomplete="username"
                                required
                            >
                        </div>

                        <div class="field">
                            <label
                                class="form-label required"
                                for="email"
                            >
                                Registered Email
                            </label>

                            <input
                                class="input"
                                id="email"
                                name="email"
                                type="email"
                                maxlength="255"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <button
                            class="btn btn-primary btn-block"
                            type="submit"
                        >
                            Send OTP
                        </button>
                    </form>

                <?php else: ?>

                    <form
                        method="post"
                        action="<?= e(dr_url('actions/verify-otp.php')) ?>"
                        class="auth-form-spaced"
                    >
                        <input
                            type="hidden"
                            name="_csrf"
                            value="<?= e(csrf_token()) ?>"
                        >

                        <div class="field">
                            <label
                                class="form-label required"
                                for="otp"
                            >
                                6-digit OTP
                            </label>

                            <input
                                class="input otp-code"
                                id="otp"
                                name="otp"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                autocomplete="one-time-code"
                                required
                                autofocus
                            >
                        </div>

                        <?php
                        if (
                            cfg('app.dev_otp_mode', false)
                            && !empty($_SESSION['dev_otp_hint'])
                        ):
                        ?>
                            <div class="auth-note">
                                Local development OTP:
                                <strong>
                                    <?= e($_SESSION['dev_otp_hint']) ?>
                                </strong>.
                                This must be disabled on production.
                            </div>
                        <?php endif; ?>

                        <button
                            class="btn btn-primary btn-block"
                            type="submit"
                        >
                            Verify OTP
                        </button>
                    </form>

                    <div class="otp-box">
                        <form
                            method="post"
                            action="<?= e(dr_url('actions/send-otp.php')) ?>"
                            class="inline-form"
                        >
                            <input
                                type="hidden"
                                name="_csrf"
                                value="<?= e(csrf_token()) ?>"
                            >

                            <input
                                type="hidden"
                                name="ien"
                                value="<?= e($_SESSION['otp_pending_ien'] ?? '') ?>"
                            >

                            <input
                                type="hidden"
                                name="email"
                                value="<?= e($_SESSION['otp_pending_email'] ?? '') ?>"
                            >

                            <button
                                id="resendOtpButton"
                                class="btn btn-secondary btn-sm"
                                type="submit"
                                <?= $otpResendRemaining > 0 ? 'disabled' : '' ?>
                            >
                                <?= $otpResendRemaining > 0
                                    ? 'Resend OTP (' . $otpResendRemaining . ')'
                                    : 'Resend OTP'
                                ?>
                            </button>
                        </form>

                        <a
                            class="admin-link"
                            href="<?= e(dr_url('index.php?reset=1')) ?>"
                        >
                            Start over
                        </a>
                    </div>

                    <div
                        class="otp-resend-note"
                        id="resendOtpNote"
                        data-resend-at="<?= e((string) $otpResendAt) ?>"
                    >
                        <?= $otpResendRemaining > 0
                            ? 'Please wait before requesting another code.'
                            : 'You can request another code if needed.'
                        ?>
                    </div>

                <?php endif; ?>

                <p class="help auth-help">
                    For help, contact the institute administration.
                    Never share your OTP.
                </p>
            </section>
        </div>
    </main>
</div>

<?php page_end(); ?>
