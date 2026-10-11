# Database migration guide

## Fresh install

1. Create/select the target MySQL or MariaDB database.
2. Import `database/schema.sql`.
3. Import `database/sample-data.sql` only for local/demo records.
4. Create `.env` from `.env.example` and set the database credentials.

The fresh schema already includes:

- core DigiResult tables;
- OTP/security tables;
- result publication;
- audit logs;
- administrator password-reset support.

## Existing DigiResult database

1. Back up the existing database.
2. Keep the existing production SQL dump separately; it is not shipped in the cleaned application package.
3. Apply migrations in order:

```text
001_security_publication.sql
002_recommended_indexes.sql   (review before applying)
003_integrity_constraints.sql (review before applying)
004_student_activation.sql
005_admin_password_reset.sql
```

The optional index and integrity migrations should be checked against the actual legacy database before execution. In particular, verify existing duplicate/orphaned records before adding new foreign-key constraints.

The application keeps the existing core table names and subject structure. Current subjects remain scoped by:

```text
Batch + Semester + Department
```

Year is stored in the student/result/publication context and is intentionally not added to `subjects` during this cleanup.

## Administrator password compatibility

The application can authenticate an existing legacy plaintext admin credential once and then replace it with a secure password hash.

Do not copy an old plaintext password into the cleaned source tree.

## Important

Do not import `database/schema.sql` over an existing production database. Use the existing database plus the required migrations instead.

Before each schema change:

```text
1. Back up the database.
2. Run the migration in a staging/test database when possible.
3. Verify row counts and admin access.
4. Verify student OTP, result editing, publication and certificate rendering.
```
