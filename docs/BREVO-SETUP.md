# DigiResult Brevo Setup

DigiResult uses the Brevo HTTPS transactional email API for:

- student OTP messages;
- administrator password-reset messages;
- administrator test emails.

## Configuration strategy

PHP does not automatically read `.env`. DigiResult explicitly loads it through:

```text
config/config.php
    ↓
config/env.php
    ↓
.env
```

Create `.env` from `.env.example`.

Server-provided environment variables take precedence over `.env` values.

The key production variables are:

```text
MAIL_MODE=brevo_api
BREVO_API_URL=https://api.brevo.com/v3/smtp/email
BREVO_API_KEY=YOUR_NEW_BREVO_API_KEY
FROM_EMAIL=your-verified-sender@example.com
FROM_NAME=DigiResult
REPLY_TO=
TEST_RECIPIENT=
```

## Local XAMPP

For a real Brevo test, use:

```text
APP_ENV=local
APP_DEV_OTP_MODE=false
MAIL_MODE=brevo_api
BREVO_API_KEY=YOUR_NEW_BREVO_API_KEY
FROM_EMAIL=your-verified-sender@example.com
```

The sender must be valid and verified in Brevo.

After changing Windows environment variables with `setx`, fully restart XAMPP/Apache so the process receives them.

To avoid external mail delivery during local development, use:

```text
APP_ENV=local
APP_DEV_OTP_MODE=true
```

The generated OTP is then shown in the local student verification page.

## Password-reset test

1. Open the administrator login page.
2. Click **Forgot password?**
3. Enter the administrator email stored in `admincreds.user_id`.
4. Submit the request.
5. Confirm the reset message arrives.
6. Open the reset link and set a new password.
7. Sign in again with the new password.

When Brevo accepts a transactional message, DigiResult records the returned `messageId` for diagnostics/auditing. The normal password-reset success message does not expose that provider identifier to the end user.

## Test transactional email

After admin login:

```text
Admin → System Health → Test transactional email
```

This isolates Brevo configuration from the student OTP flow.

Check:

```text
Brevo API key
Verified sender
cURL / HTTPS access
Provider response
```

## Security

Never commit a Brevo API key to GitHub.

The cleaned project contains no real API key.

Any key that was previously exposed should be revoked and replaced with a newly generated key.
