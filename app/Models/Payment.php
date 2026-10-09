<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['billing_id', 'payment_date', 'amount', 'method', 'reference'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount'       => 'decimal:2',
        ];
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
