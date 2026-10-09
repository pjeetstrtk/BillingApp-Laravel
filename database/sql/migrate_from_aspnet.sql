-- ---------------------------------------------------------------------------
-- OPTIONAL: copy the data of the old ASP.NET Core app into the Laravel schema.
--
-- 1. Create the new database and run `php artisan migrate` (do NOT seed yet).
-- 2. Edit the two database names below if yours differ:
--      old (ASP.NET / EF Core): billingdb
--      new (Laravel)          : billing_laravel
-- 3. Run this file against the same MySQL server, e.g.
--      mysql -u root -p < database/sql/migrate_from_aspnet.sql
--
-- Existing ids, amounts, invoice numbers and BCrypt password hashes are kept,
-- so current logins keep working. Run it once, on empty Laravel tables.
-- ---------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `billing_laravel`.`users` (`id`, `username`, `password`, `role`, `created_at`, `updated_at`)
SELECT `Id`, `Username`, `PasswordHash`, `Role`, NOW(), NOW()
FROM `billingdb`.`Users`;

INSERT INTO `billing_laravel`.`customers` (`id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`)
SELECT `Id`, `Name`, `Email`, `Phone`, `Address`, `CreatedAt`, `CreatedAt`
FROM `billingdb`.`Customers`;

INSERT INTO `billing_laravel`.`products` (`id`, `name`, `description`, `price`, `stock`, `created_at`, `updated_at`)
SELECT `Id`, `Name`, `Description`, `Price`, `Stock`, `CreatedAt`, `CreatedAt`
FROM `billingdb`.`Products`;

INSERT INTO `billing_laravel`.`billings`
    (`id`, `invoice_no`, `customer_id`, `bill_date`, `total_amount`, `paid_amount`, `balance_amount`, `status`, `created_at`, `updated_at`)
SELECT `Id`, `InvoiceNo`, `CustomerId`, `BillDate`, `TotalAmount`, `PaidAmount`, `BalanceAmount`, `Status`, `BillDate`, `BillDate`
FROM `billingdb`.`Billings`;

INSERT INTO `billing_laravel`.`billing_items`
    (`id`, `billing_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`)
SELECT bi.`Id`, bi.`BillingId`, bi.`ProductId`, bi.`Quantity`, bi.`UnitPrice`, bi.`LineTotal`, b.`BillDate`, b.`BillDate`
FROM `billingdb`.`BillingItems` bi
JOIN `billingdb`.`Billings` b ON b.`Id` = bi.`BillingId`;

INSERT INTO `billing_laravel`.`payments`
    (`id`, `billing_id`, `payment_date`, `amount`, `method`, `reference`, `created_at`, `updated_at`)
SELECT `Id`, `BillingId`, `PaymentDate`, `Amount`, `Method`, `Reference`, `PaymentDate`, `PaymentDate`
FROM `billingdb`.`Payments`;

SET FOREIGN_KEY_CHECKS = 1;
