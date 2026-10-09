<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads the 100-row sample data set (customers, products, users, billings,
 * billing items, payments) from database/sql/sample_data.sql.
 *
 *   php artisan db:seed --class=SampleDataSeeder
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('sql/sample_data.sql');

        DB::unprepared(file_get_contents($path));
    }
}
