# BillingApp (Laravel 11)

Laravel 11 / PHP 8.3+ / PostgreSQL port of the ASP.NET Core 8 BillingApp:
customers, products, invoices with stock tracking, payments, dashboard,
date-range sales report, and PDF invoices / reports.

## Requirements

- PHP **8.3+** with `mbstring`, `openssl`, `pdo_pgsql`, `xml`/`dom`, `ctype`, `fileinfo`, `tokenizer` (and `gd` is recommended for dompdf)
- Composer 2
- PostgreSQL 13+ (tested with 16)

## Setup

> `composer.json` sets `audit.block-insecure = false` so Composer 2.9+ will install Laravel 11
> (which no longer receives security fixes). Fine for local use; for production, move to Laravel 12:
> `composer require "laravel/framework:^12.0" --no-update && composer update`.

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Create a database (psql or pgAdmin):

```sql
CREATE DATABASE billing_laravel;
```

Set `DB_USERNAME` / `DB_PASSWORD` in `.env` (defaults: `postgres`, port 5432), then:

```bash
php artisan migrate --seed          # tables + admin user (admin / admin123)
php artisan db:seed --class=SampleDataSeeder   # optional: the 100-row sample data
php artisan serve                   # http://localhost:8000
```

Log in with **admin / admin123** and change the password afterwards.

If you get `could not find driver`, enable `extension=pdo_pgsql` and `extension=pgsql` in `php.ini`.

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
- CLI: `php artisan db:backup` writes a plain-SQL `pg_dump` file to `storage/app/backups`
  and keeps the newest 14 (`BACKUP_KEEP`).
- Runs nightly at 02:00 through the scheduler: keep `php artisan schedule:work` running, or add a
  Task Scheduler / cron job that runs `php artisan schedule:run` every minute.
- On Windows set `PG_DUMP_PATH` in `.env` to the full path of `pg_dump.exe`
  (e.g. `C:\Program Files\PostgreSQL\16\bin\pg_dump.exe`); its version must not be older than your server.
- Restore: `psql -U postgres -d billing_laravel -f storage/app/backups/<file>.sql`
  (dumps use `--clean --if-exists`, so they can be restored over an existing database).

### Tests

```bash
php artisan test        # uses in-memory SQLite (needs pdo_sqlite), never touches PostgreSQL
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
| `appsettings.json` connection string (MySQL) | `.env` (`DB_*`) |
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
