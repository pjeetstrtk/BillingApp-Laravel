<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads the 100-row sample data set (customers, products, users, billings,
 * billing items, payments) from database/seeders/data/sample_data.json.
 * Works on SQLite, MySQL and PostgreSQL, and is safe to run more than once.
 *
 *   php artisan db:seed --class=SampleDataSeeder
 */
class SampleDataSeeder extends Seeder
{
    /** Parents before children so foreign keys are satisfied. */
    private const TABLES = ['customers', 'products', 'users', 'billings', 'billing_items', 'payments'];

    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/sample_data.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            foreach (self::TABLES as $table) {
                foreach (array_chunk($data[$table], 50) as $chunk) {
                    // insertOrIgnore = INSERT IGNORE (MySQL) / ON CONFLICT DO NOTHING (PostgreSQL) / INSERT OR IGNORE (SQLite)
                    DB::table($table)->insertOrIgnore($chunk);
                }
            }

            $this->resetPostgresSequences();
        });
    }

    /** PostgreSQL does not advance its id sequences when ids are inserted explicitly. */
    private function resetPostgresSequences(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))");
        }
    }
}
