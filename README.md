# BillingApp (Laravel 11)

Laravel 11 / PHP 8.3+ / MySQL 8.0 port of the ASP.NET Core 8 BillingApp:
customers, products, invoices with stock tracking, payments, dashboard,
date-range sales report, and PDF invoices / reports.

## Requirements

- PHP **8.3+** with `mbstring`, `openssl`, `pdo_mysql`, `xml`/`dom`, `ctype`, `fileinfo`, `tokenizer` (and `gd` is recommended for dompdf)
- Composer 2
- MySQL **8.0.33** (any 8.0.x works)

## Setup

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Create a **new** database (don't reuse the ASP.NET one - see note below):

```sql
CREATE DATABASE billing_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Adjust `DB_USERNAME` / `DB_PASSWORD` in `.env`, then:

```bash
php artisan migrate --seed          # tables + admin user (admin / admin123)
php artisan db:seed --class=SampleDataSeeder   # optional: the 100-row sample data
php artisan serve                   # http://localhost:8000
```

Log in with **admin / admin123** and change the password afterwards.

### Bringing over existing data (optional)

If you already have data in the old `billingdb`, skip `--seed` / the sample seeder and run
`database/sql/migrate_from_aspnet.sql` after `php artisan migrate`. Ids, amounts, invoice
numbers and password hashes are copied as-is.

> The old schema uses PascalCase tables (`Customers`, `BillingItems` ...). On Windows,
> MySQL treats table names case-insensitively, so keep the Laravel schema in its own
> database to avoid clashes with the old tables.

### Tests

```bash
php artisan test        # uses in-memory SQLite (needs pdo_sqlite), never touches MySQL
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
