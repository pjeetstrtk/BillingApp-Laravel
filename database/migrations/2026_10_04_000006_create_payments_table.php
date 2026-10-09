<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // Cascade: deleting an invoice removes its payments
            $table->foreignId('billing_id')->constrained('billings')->cascadeOnDelete();
            $table->dateTime('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('method', 30)->default('Cash');
            $table->string('reference', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
