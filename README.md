# DigiResult

DigiResult is a traditional PHP + MySQL/MariaDB student result management portal for New Horizon Institute of Technology and Management. This cleanup keeps the current working application, its existing behavior, its green visual language, and its low-cost PHP-hosting deployment model.

The project is intentionally **not** being migrated to Laravel, React, Node.js, or another application framework.

---

## 1. Project overview

DigiResult has two protected flows:

```text
Student
IEN + registered email
        ↓
OTP request
        ↓
Brevo transactional email
        ↓
OTP verification
        ↓
Secure student session
        ↓
Published result
        ↓
Certificate / Grade Card
        ↓
Print / Save PDF

Administrator
Login
  ↓
Dashboard
  ↓
Students / Academic Setup / Results
  ↓
Edit marks
  ↓
Preview
  ↓
Publish / Unpublish
```

The cleanup stage focuses on code organization, CSS separation, deployment portability, secret handling, documentation accuracy, and dependency-safe removal of dead artifacts.

---

## 2. Features

### Student portal

- Student identity verification using IEN number + registered email.
- Server-generated six-digit OTP.
- Hashed OTP storage.
- OTP expiry and attempt limits.
- Resend cooldown and request throttling.
- MySQL-based OTP timing.
- Secure session regeneration after verification.
- Protected student dashboard and result pages.
- Published-result visibility control.
- Dynamic certificate subject rows.
- Browser-based Print / Save PDF.

### Administrator portal

- Administrator login.
- Password hashing and one-time legacy plaintext-password upgrade.
- Dashboard.
- Student creation, editing, activation/deactivation, and deletion.
- Academic setup for Year, Batch, Semester, Department, and Subjects.
- Subject filters and subject-configuration CRUD.
- Result editor for TW / PR / ESE.
- Result preview.
- Result publication and unpublication.
- Administrator password change.
- Forgot-password → reset-link → new-password flow.
- Logout confirmation.
- System health checks.
- Audit logging.

### Deployment-oriented improvements

- Explicit `.env` loading without Composer.
- Secret-free centralized configuration and explicit `.env` loading.
- Lowercase `certificate.png`.
- External CSS files instead of PHP `<style>` blocks and inline page styles.
- Safe Apache protection for application internals.
- Removal of unused test artifacts and compatibility files after reference auditing.
- Legacy URL redirects moved to `.htaccess`.

---

## 3. Technology stack

| Layer | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL / MariaDB |
| Database API | `mysqli` |
| Frontend | HTML, CSS, Vanilla JavaScript |
| Email | Brevo HTTPS transactional API |
| Local development | XAMPP on Windows |
| Hosting target | ByetHost / similar low-cost Apache + PHP hosting |

No Laravel, Node.js, npm, React, Vue, Composer, or frontend build step is required.

---

## 4. Final folder structure

```text
DigiResult/
├── .env.example
├── .gitignore
├── .htaccess
├── README.md
├── index.php
├── certificate.php
│
├── actions/
│   ├── academic-delete.php
│   ├── academic-save.php
│   ├── admin-forgot-password.php
│   ├── admin-login.php
│   ├── admin-password.php
│   ├── admin-reset-password.php
│   ├── result-publish.php
│   ├── result-save.php
│   ├── send-otp.php
│   ├── send-test-email.php
│   ├── student-delete.php
│   ├── student-save.php
│   ├── student-toggle.php
│   ├── subject-delete.php
│   ├── subject-save.php
│   └── verify-otp.php
│
├── admin/
│   ├── login.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── account.php
│   ├── health.php
│   ├── academic/
│   │   └── index.php
│   ├── results/
│   │   ├── index.php
│   │   ├── editor.php
│   │   └── view.php
│   └── students/
│       ├── index.php
│       ├── create.php
│       └── edit.php
│
├── student/
│   ├── dashboard.php
│   ├── result.php
│   └── logout.php
│
├── config/
│   ├── config.php
│   ├── config.example.php
│   ├── database.php
│   └── env.php
│
├── includes/
│   ├── bootstrap.php
│   ├── functions.php
│   ├── auth.php
│   ├── layout.php
│   ├── admin-queries.php
│   └── student-queries.php
│
├── services/
│   ├── mailer.php
│   └── templates/
│       ├── otp-email.html
│       ├── password-reset-email.html
│       └── test-email.html
│
├── assets/
│   ├── css/
│   │   ├── app.css
│   │   ├── auth.css
│   │   ├── admin.css
│   │   ├── student.css
│   │   └── certificate.css
│   ├── js/
│   │   └── app.js
│   └── images/
│       ├── certificate.png
│       └── logo.webp
│
├── database/
│   ├── schema.sql
│   ├── sample-data.sql
│   ├── README-MIGRATION.md
│   └── migrations/
│       ├── 001_security_publication.sql
│       ├── 002_recommended_indexes.sql
│       ├── 003_integrity_constraints.sql
│       ├── 004_student_activation.sql
│       └── 005_admin_password_reset.sql
│
└── docs/
    └── BREVO-SETUP.md
```

Runtime-generated storage/log files are not packaged as application artifacts.

---

## 5. Architecture

The application follows a simple server-rendered structure:

```text
Page
  ↓
includes/bootstrap.php
  ↓
config + database + shared helpers + authentication
  ↓
query/helper layer
  ↓
HTML response

State-changing request
  ↓
actions/*.php
  ↓
CSRF + validation + prepared SQL
  ↓
database
  ↓
flash message + redirect
```

Responsibilities are intentionally separated:

- `config/` loads deployment configuration and database credentials.
- `includes/` contains bootstrap, authentication, shared helpers, layout functions, and query helpers.
- `services/` contains transactional mail delivery and email templates.
- `actions/` contains POST/state-changing operations.
- `admin/` contains administrator UI.
- `student/` contains authenticated student UI.
- `assets/css/` contains presentation only.
- `assets/js/` contains browser interaction only.
- `database/` contains schema and migrations.

---

## 6. Authentication flow

### Student

```text
1. Student enters IEN + registered email.
2. Server verifies the student identity.
3. Server generates a six-digit OTP.
4. OTP is stored only as a password hash.
5. OTP expiry, request limits, resend cooldown and attempts are enforced.
6. Brevo sends the OTP when normal mail mode is active.
7. Student enters the OTP.
8. Server verifies the hash and expiry.
9. Session ID is regenerated.
10. Student is redirected to the protected dashboard.
```

In local development, `APP_DEV_OTP_MODE=true` can display the generated OTP in the browser so the flow can be tested without mail delivery.

### Administrator

```text
Login
  ↓
Password verification
  ↓
Secure admin session
  ↓
Protected admin pages
```

Legacy plaintext administrator passwords are supported only for compatibility during login and are upgraded to `password_hash()` after a successful authentication.

---

## 7. OTP / Brevo configuration

Configuration is loaded explicitly from `.env` by `config/env.php`. No Composer dotenv package is required.

The important environment variables are:

```text
APP_ENV=production
APP_BASE_PATH=
APP_DEV_OTP_MODE=false

DB_HOST=
DB_PORT=3306
DB_NAME=
DB_USER=
DB_PASSWORD=

MAIL_MODE=brevo_api
BREVO_API_URL=https://api.brevo.com/v3/smtp/email
BREVO_API_KEY=
FROM_EMAIL=
FROM_NAME=DigiResult
REPLY_TO=
TEST_RECIPIENT=
```

Security/OTP values are also configurable:

```text
OTP_TTL_MINUTES=10
OTP_RESEND_SECONDS=60
OTP_MAX_REQUESTS_15M=5
OTP_MAX_ATTEMPTS=5
ADMIN_PASSWORD_RESET_TTL_MINUTES=15
```

For hosted production:

```text
APP_ENV=production
APP_DEV_OTP_MODE=false
MAIL_MODE=brevo_api
BREVO_API_KEY=<real server-side secret>
FROM_EMAIL=<verified sender>
```

Never place a real API key in GitHub, screenshots, public configuration, or browser code.

A failed Brevo send removes the newly-created OTP record so the user is not unnecessarily trapped behind a resend cooldown after a delivery failure.

---

## 8. Administrator password reset

The password recovery flow is:

```text
Admin login
   ↓
Forgot password
   ↓
Email lookup
   ↓
Random reset token
   ↓
SHA-256 token hash stored in MySQL
   ↓
Brevo email
   ↓
Reset URL
   ↓
New password
   ↓
Reset token marked used
   ↓
Old reset tokens cleaned
   ↓
Admin signs in again
```

Reset tokens expire automatically and are single-use.

The application records a Brevo `messageId` when the provider returns one for diagnostics/auditing. The normal user-facing password-reset success message does not expose provider internals.

For a fresh database, `database/schema.sql` already includes the password-reset table.

For an existing database, apply:

```text
database/migrations/005_admin_password_reset.sql
```

---

## 9. Database setup

### Fresh install

1. Create a MySQL/MariaDB database.
2. Import `database/schema.sql`.
3. Optionally import `database/sample-data.sql`.
4. Configure `.env`.
5. Open the application.

`schema.sql` includes the current core tables, security tables, result publication table, audit table, and administrator password-reset table.

### Sample account

The sample-data file contains:

```text
Email: admin@example.com
Password: change-me
```

Change this password immediately after the first login.

---

## 10. Existing database migration

For an existing DigiResult installation:

1. Back up the production database.
2. Keep the original database dump separately; it is not included in this cleanup package.
3. Apply `001_security_publication.sql`.
4. Apply `004_student_activation.sql`.
5. Apply `005_admin_password_reset.sql`.
6. Review `002_recommended_indexes.sql` before adding optional indexes.
7. Review `003_integrity_constraints.sql` after checking legacy duplicates, orphaned rows, and foreign-key compatibility.
8. Point the deployment at the existing database through `.env`.
9. Log in once with any legacy plaintext admin credential, when applicable, so the application can upgrade it to a password hash.

The cleanup does **not** change the subject schema to add Year.

Current subject configuration is scoped by:

```text
Batch
Semester
Department
```

Year is part of the student/result/publication context separately.

A Year column should only be introduced later after demonstrating that the same Batch + Semester + Department combination needs different subject catalogues across years and coordinating the change across subject CRUD, filters, result editing, result saving, and certificate queries. No such migration is introduced in this cleanup because doing so blindly could split or orphan existing subject data.

---

## 11. XAMPP setup

Place the project at:

```text
C:\xampp\htdocs\DigiResult
```

Start:

```text
Apache
MySQL
```

Create a database named:

```text
srms
```

Import:

```text
database/schema.sql
database/sample-data.sql
```

Copy:

```text
.env.example
```

to:

```text
.env
```

Then set local values:

```text
APP_ENV=local
APP_BASE_PATH=/DigiResult
APP_DEV_OTP_MODE=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=srms
DB_USER=root
DB_PASSWORD=
```

With `APP_DEV_OTP_MODE=true`, the application can exercise the student OTP flow without depending on a mail server.

Open:

```text
http://localhost/DigiResult/
```

Admin:

```text
http://localhost/DigiResult/admin/login.php
```

For a real Brevo test on XAMPP, set `APP_DEV_OTP_MODE=false`, provide a valid Brevo API key and verified sender, then use **Admin → System Health → Test transactional email**.

---

## 12. ByetHost deployment

The project is kept compatible with a normal Linux Apache/PHP shared-hosting environment.

### Upload

1. Create/select the hosted site.
2. Enable HTTPS.
3. Upload the project files into the web root.
4. Do not upload a development `.env` containing local credentials.
5. Create the production `.env` on the server.
6. Confirm `.htaccess` is enabled by the hosting account.

### Production `.env`

Use:

```text
APP_ENV=production
APP_BASE_PATH=
APP_DEV_OTP_MODE=false

DB_HOST=<hosting database host>
DB_PORT=3306
DB_NAME=<hosting database name>
DB_USER=<hosting database user>
DB_PASSWORD=<hosting database password>

MAIL_MODE=brevo_api
BREVO_API_KEY=<server-side Brevo API key>
FROM_EMAIL=<verified sender>
FROM_NAME=DigiResult
REPLY_TO=
```

The exact database hostname, database name, username, and password must come from the hosting control panel.

### Database

For a new deployment:

```text
database/schema.sql
```

For an existing database:

```text
existing production database
+
required migration files
```

Do not rebuild an existing production database by importing `schema.sql` over live data.

### Mail

Use the Brevo API path for the intended low-cost deployment. Do not depend on PHP `mail()` or SMTP unless your particular hosting plan explicitly supports and permits them.

The app's System Health page can verify cURL/HTTPS access and the configured Brevo sender/key before a real student OTP is tested.

---

## 13. GitHub setup

The project is suitable for a normal Git repository.

Before pushing:

```bash
git init
git add .
git commit -m "Clean and deployment-harden DigiResult"
git branch -M main
git remote add origin <your-repository-url>
git push -u origin main
```

The repository must not contain:

```text
.env
production `.env` or server environment variables containing secrets
real Brevo API keys
real database passwords
```

The included `.gitignore` already ignores `.env` and `config/config.php`.

---

## 14. Security requirements

Keep these controls enabled in production:

- HTTPS.
- Prepared `mysqli` statements.
- CSRF validation on state-changing forms.
- Output escaping through the shared HTML escaping helper.
- Secure PHP sessions with session regeneration after authentication.
- Password hashing with `password_hash()`.
- Hash-only OTP storage.
- OTP expiry, attempt limits, cooldowns, and request throttling.
- Hashed administrator reset tokens with expiry and single-use behavior.
- Protected admin/student routes.
- Apache protection for config, includes, services, database, storage, logs, SQL, `.env`, and similar files.
- Lowercase paths that behave consistently on Linux.
- No Windows absolute paths in application URLs.
- No real secrets in Git.

Do not disable the `.htaccess` internal-directory protections.

---

## 15. Configuration / environment strategy

The application does not assume that PHP automatically reads `.env`.

Instead:

```text
config/config.php
      ↓
config/env.php
      ↓
.env
      ↓
runtime configuration
```

Existing server environment variables take precedence over values loaded from `.env`.

Use:

```text
.env.example
```

as the shareable template.

Use:

```text
.env
```

for real deployment values.

Use:

```text
config/config.example.php
```

as the shareable PHP configuration shape.

Keep real deployment configuration outside Git.

---

## 16. Certificate behavior

`certificate.php` is the visually sensitive page and its current positioning is preserved.

The base artwork is:

```text
assets/images/certificate.png
```

It is the lowercase-normalized copy of the supplied working certificate template.

The certificate dynamically obtains subject rows from:

```text
get_certificate_rows_for_student()
```

The query combines:

```text
configured subject catalogue
+
legacy/result subjects for the exact academic context
+
this student's saved marks
```

This means configured subjects still appear even when the current student has no marks entered for them.

Current displayed data is limited to fields actually available in the database, including:

```text
Student name
Seat / student number
IEN
Semester
Year
Department / branch
Subject
TW
PR
ESE
Calculated raw total
```

Unknown official fields are intentionally not fabricated.

The following are not invented:

```text
Course code
Course credits
Credit earned
Official grade
Grade point
C × G
SGPI
CGPI
Examination date
Serial number
```

The certificate uses semantic subject-row classes in `certificate.css` for different subject counts instead of inline CSS variables.

---

## 17. Result publication

The admin Results page preserves separate unique dropdown values for:

```text
Year
Batch
Semester
Department
```

A Year therefore appears only once even when it occurs in several academic combinations.

Publishing is scoped to:

```text
Year + Batch + Semester + Department
```

A published result is visible to the authenticated student flow only when the matching result context is published.

The Result Editor continues to use:

```text
TW
PR
ESE
```

and keeps editable marks, CSRF protection, save behavior, and the centered `Not saved` state.

---

## 18. Legacy URL handling

Physical compatibility files were removed after the dependency audit showed that the running application no longer requires them.

The old routes that represented application pages are redirected through `.htaccess` instead of keeping duplicate PHP files.

Examples include:

```text
Admin.html
Login_b.php
Student_Add.php
Student_Update.php
Student_Delete.html
Batch_Details.php
fetch_student.php
student.php
toggle_live.php
Update_Batch_1.php
Admin_Results_Details_1.php
Admin_Results_Details_2.php
Result_Page.php
view_results.php
Certificate.php
```

`connection.php` and `connection.example.php` were removed because the application now uses the centralized `config/database.php` path and no internal runtime dependency remained.

No `Admin_Results_Details_3.php` or `Admin_Results_Details_4.php` files remain.

---

## 19. Troubleshooting

### PHP page shows a database error

Check:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
```

Then confirm the MySQL/MariaDB service is running.

### Student OTP is not arriving

Check:

```text
APP_DEV_OTP_MODE=false
MAIL_MODE=brevo_api
BREVO_API_KEY=<valid key>
FROM_EMAIL=<verified sender>
```

Then run the System Health email test before testing student verification.

### Local OTP testing

Set:

```text
APP_ENV=local
APP_DEV_OTP_MODE=true
```

The generated OTP is then shown in the local browser flow rather than sent through Brevo.

### Admin reset link fails

Check that:

- migration `005_admin_password_reset.sql` is installed for an existing database;
- the reset link has not expired;
- the link has not already been used;
- the administrator email exactly matches the configured admin account.

### Certificate image is missing on Linux hosting

Check that the file is exactly:

```text
assets/images/certificate.png
```

Linux hosts are case-sensitive.

### Old local path appears in a URL

Use application URLs generated by `dr_url()` and keep:

```text
APP_BASE_PATH=/DigiResult
```

for the standard XAMPP folder, or leave it empty when the application is deployed at the domain root.

---

## 20. Known limitations

- The application remains a traditional PHP + `mysqli` codebase by design.
- The certificate does not calculate official grade points, SGPI, or CGPI because the current database does not provide the authoritative grading formula and fields.
- Subject curricula are currently scoped by Batch + Semester + Department rather than Year + Batch + Semester + Department.
- Certificate rendering uses the supplied fixed grade-card artwork and browser print-to-PDF rather than server-side PDF generation.
- Brevo delivery depends on valid provider credentials, a verified sender, and working outbound HTTPS from the hosting environment.
- No Composer dependency or framework abstraction was added merely for modernization.

---

## 21. Future certificate/database fields

These fields are deliberately left for a later, coordinated schema change:

```text
Course code
Course credits
Credit earned
Official grade
Grade point
C × G
SGPI
CGPI
Examination date
Certificate serial number
```

When those fields are introduced, update the database schema, result editor, queries, certificate mapping, validation rules, and documentation together.

---

## Change-control principle

This cleanup is intentionally conservative:

> Preserve the current DigiResult behavior first. Improve organization, maintainability, security hygiene, and deployment portability without changing the application's working business rules merely for modernization.
