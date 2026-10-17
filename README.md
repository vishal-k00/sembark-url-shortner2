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
- Email: `superadmin@sembark.com`
- Password: `password123`



## Tests
Run `php artisan test`. The feature tests cover Admin/Member URL creation, SuperAdmin denial, Admin company scoping, Member creator scoping, and public redirects/click counts.

## Role rules
- `super_admin`: can see URLs across companies and create companies by inviting an Admin; cannot create short URLs.
- `admin`: can create URLs, view URLs for their own company, and invite Admins or Members into their company.
- `member`: can create URLs and view only their own URLs.
- Public short links redirect without authentication.

