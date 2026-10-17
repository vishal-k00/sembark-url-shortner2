# Sembark URL Shortener (Laravel 12)

A multi-tenant URL shortener implementing the core requirements in the Sembark Tech backend assignment: role-based access, company boundaries, invitations, URL creation, public redirects, click counts, and feature tests.

## Requirements
- PHP 8.2+
- Composer 2
- SQLite (default) or MySQL

## Local setup
1. Install dependencies: `composer install`
2. Copy environment: `cp .env.example .env`
3. Generate application key: `php artisan key:generate`
4. For SQLite, create an empty database file: `touch database/database.sqlite` and ensure `.env` contains `DB_CONNECTION=sqlite`.
5. Run migrations and seed the SuperAdmin: `php artisan migrate --seed`
6. Start the app: `php artisan serve`
7. Open `http://127.0.0.1:8000/login`.

Seeded local SuperAdmin credentials:
- Email: `superadmin@example.com`
- Password: `ChangeMe-Immediately-123!`

**Change/remove the demo SuperAdmin password before deploying.** The seeder intentionally uses a raw SQL statement to meet the assignment requirement. Re-running the seeder will fail on the unique email; use a fresh database or remove the existing seeded account first.

## Tests
Run `php artisan test`. The feature tests cover Admin/Member URL creation, SuperAdmin denial, Admin company scoping, Member creator scoping, and public redirects/click counts.

## Role rules
- `super_admin`: can see URLs across companies and create companies by inviting an Admin; cannot create short URLs.
- `admin`: can create URLs, view URLs for their own company, and invite Admins or Members into their company.
- `member`: can create URLs and view only their own URLs.
- Public short links redirect without authentication.

The assignment lists `sales` and `manager` as roles but doesn't define their permissions. They are not granted additional access in this implementation; define their exact behavior if the company asks.

## Invitation workflow
For assignment/demo simplicity, the app displays the invitation acceptance URL after creating an invitation instead of sending email. The URL expires after 3 days, is stored as a SHA-256 token hash, and the invitee sets a password of at least 12 characters. In production, send the link via a queued mail notification and avoid exposing it in a dashboard flash message.

## Design and scalability notes
- URL queries are scoped server-side by company and creator, not just hidden in the UI.
- Short codes are generated with random characters, backed by a unique database index. For very high concurrency, replace the pre-check loop with collision handling on unique-constraint violations.
- Click counts use an atomic database increment. At high traffic, move click events to a queue/event table and aggregate asynchronously.
- Add rate limiting to login, invitation creation, and URL generation before public deployment.
- Use HTTPS, secure cookie settings, mail delivery, monitoring, and a production database configuration before deployment.

## AI assistance disclosure
ChatGPT was used to help draft the initial project structure, implementation examples, README, and test cases. The candidate should review the code, run the full test suite, understand each design decision, and revise anything they cannot explain before submitting, consistent with the assignment's AI policy.

## Before submission checklist
1. Run `composer install`, `php artisan key:generate`, `php artisan migrate:fresh --seed`, and `php artisan test` on your own PHP environment.
2. Log in as SuperAdmin and create a company by inviting its Admin. Open the generated invitation link in a private browser window, set a password, and log in.
3. Create a short URL as Admin, invite a Member, and verify the Member can only see their own links.
4. Test the public short URL in a logged-out browser.
5. Commit the source to a public GitHub repository and include the repository URL in your submission.
6. Be prepared to explain the tenant-scoped query, role checks, unique short-code index, transaction in invitation acceptance, and each feature test.

## Environment validation note
This source bundle was syntax-checked with PHP 8.4. The generation environment did not have Composer or the PDO SQLite extension installed, so Laravel dependencies could not be installed and the feature test suite could not be executed here. Run the commands above before submitting; fix any environment-specific issues you encounter.
