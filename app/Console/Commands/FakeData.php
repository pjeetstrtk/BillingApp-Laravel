<?php

namespace App\Console\Commands;

use Database\Seeders\FakeDataSeeder;
use Illuminate\Console\Command;

class FakeData extends Command
{
    protected $signature = 'fake:data
                            {--customers=30 : Number of customers}
                            {--products=40 : Number of products}
                            {--invoices=60 : Number of invoices}';

    protected $description = 'Fill the database with Faker-generated customers, products, invoices and payments';

    public function handle(): int
    {
        if (! app()->environment('local', 'testing') && ! $this->confirm('This is not a local environment. Add fake data anyway?')) {
            return self::FAILURE;
        }

        app(FakeDataSeeder::class)
            ->counts((int) $this->option('customers'), (int) $this->option('products'), (int) $this->option('invoices'))
            ->run();

        $this->info('Fake data created.');

        return self::SUCCESS;
    }
}
