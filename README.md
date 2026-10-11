# BillingApp (Laravel 11)

Laravel 11 / PHP 8.3+ port (SQLite, MySQL 8 or PostgreSQL) of the ASP.NET Core 8 BillingApp:
customers, products, invoices with stock tracking, payments, dashboard,
date-range sales report, and PDF invoices / reports.

## Requirements

- PHP **8.3+** with `mbstring`, `openssl`, `xml`/`dom`, `ctype`, `fileinfo`, `tokenizer`, `gd` (recommended for dompdf) and the PDO driver for your database:
  `pdo_sqlite` (default), `pdo_mysql` or `pdo_pgsql`
- Composer 2
- Optional database server: MySQL 8.0.x (tested on 8.0.46) or PostgreSQL 13+ (tested on 16). SQLite needs no server.

## Setup

> `composer.json` sets `audit.block-insecure = false` so Composer 2.9+ will install Laravel 11
> (which no longer receives security fixes). Fine for local use; for production, move to Laravel 12:
> `composer require "laravel/framework:^12.0" --no-update && composer update`.

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

### Choose a database (in `.env`)

**SQLite (default, nothing to install)** - leave `DB_CONNECTION=sqlite` and do not set `DB_DATABASE`.
The file `database/database.sqlite` is included (Laravel creates it if missing).

**MySQL 8**
```sql
CREATE DATABASE billing_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billing_laravel
DB_USERNAME=root
DB_PASSWORD=
```

**PostgreSQL**
```sql
CREATE DATABASE billing_laravel;
```
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=billing_laravel
DB_USERNAME=postgres
DB_PASSWORD=
```

If you switch databases, run `php artisan config:clear` first. "could not find driver" means the
matching `pdo_*` extension is not enabled in `php.ini`.

Then:

```bash
php artisan migrate --seed                      # tables + admin user (admin / admin123)
php artisan db:seed --class=SampleDataSeeder    # optional: 100-row sample data (any database)
php artisan serve                               # http://localhost:8000
```

Log in with **admin / admin123** and change the password afterwards.

### Fake data (Faker)

```bash
php artisan fake:data                                   # 30 customers, 40 products, 60 invoices + payments
php artisan fake:data --customers=50 --products=60 --invoices=200
php artisan db:seed --class=FakeDataSeeder              # same thing with the defaults
```

Invoices get consistent totals, balances and statuses (unpaid / partly paid / paid). Faker is a
dev dependency, so `composer install` must not use `--no-dev`.

### Backups

- **Backups** page in the sidebar: back up now, download, delete.
- CLI: `php artisan db:backup` keeps the newest 14 (`BACKUP_KEEP`) in `storage/app/backups`.
  - **SQLite:** consistent copy of the database file (`.sqlite`, `VACUUM INTO`). No extra tools.
  - **MySQL:** `mysqldump` -> `.sql`. On Windows set `MYSQL_DUMP_PATH` to the full path of `mysqldump.exe`
    (Laragon: `D:\laragon\bin\mysql\mysql-8.0.33-winx64\bin\mysqldump.exe`).
  - **PostgreSQL:** `pg_dump` -> `.sql`. On Windows set `PG_DUMP_PATH` (its version must not be older than the server).
- Nightly at 02:00 via the scheduler: keep `php artisan schedule:work` running, or add a Task Scheduler /
  cron job running `php artisan schedule:run` every minute.
- Restore (the Backups page shows the command for your database):
  SQLite - stop the app and copy the `.sqlite` file over `database/database.sqlite`;
  MySQL - `mysql -u root -p billing_laravel < file.sql`;
  PostgreSQL - `psql -U postgres -d billing_laravel -f file.sql`.

### Tests

```bash
php artisan test        # uses in-memory SQLite (needs pdo_sqlite), never touches your real database
```

## What changed from the ASP.NET version

| ASP.NET Core | Laravel |
|---|---|
| EF Core + Pomelo, migrations, `EnsureCreated()` | Eloquent models + `database/migrations`, `php artisan migrate` |
| Manual session login (`Session["UserId"]`) + `IsLoggedIn()` in every action | Laravel auth guard (`auth` / `guest` middleware), session regenerated on login |
| `BCrypt.Net` | Laravel hasher (bcrypt) - existing `$2a$` hashes still verify and are upgraded on next login |
| Razor views / tag helpers | Blade views (same Bootstrap 5 layout) |
| QuestPDF | `barryvdh/laravel-dompdf` (Blade templates in `resources/views/pdf`) |
| `Controllers/*Controller.cs` | `app/Http/Controllers/*Controller.php`, routes in `routes/web.php` |
| `appsettings.json` connection string | `.env` (`DB_*`) |
| Admin seeded at startup | `DatabaseSeeder` |

Columns follow Laravel conventions: `PascalCase` -> `snake_case` (`InvoiceNo` -> `invoice_no`),
`PasswordHash` -> `password`, and every table has `created_at` / `updated_at`.

### Behaviour differences (deliberate)

- **Stock is checked** when creating an invoice (the old app could drive stock negative); invoice + stock update run in one DB transaction with row locks.
- Customers/products that are referenced by invoices show a message instead of crashing on the foreign key.
- Report "To" date is inclusive (the whole selected day is included); default range is last month -> today.
- Invoice numbers keep the `INV-yyyyMMddHHmmss` format, with a `-1`, `-2` ... suffix if two are created in the same second; `invoice_no` is now unique.
- Logout is a POST (CSRF-protected) and login is throttled to 10 attempts/minute.
- Validation added on forms (non-negative price/stock, positive payment amount, field lengths).
- PDFs don't show "Page X / Y" (dompdf can't do page totals without enabling inline PHP).
- Timezone defaults to `Asia/Dhaka` (`APP_TIMEZONE`); session idle lifetime is 30 minutes as before.
- The old `Customer/Delete.cshtml` accidentally contained the product list; it is now a proper confirmation page.
