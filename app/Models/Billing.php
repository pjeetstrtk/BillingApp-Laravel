<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_PARTIAL = 'Partial';
    public const STATUS_PAID    = 'Paid';

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'bill_date',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bill_date'      => 'datetime',
            'total_amount'   => 'decimal:2',
            'paid_amount'    => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** INV-yyyyMMddHHmmss, with a numeric suffix if that number is already taken. */
    public static function generateInvoiceNo(): string
    {
        $base = 'INV-' . now()->format('YmdHis');
        $number = $base;
        $i = 1;

        while (static::where('invoice_no', $number)->exists()) {
            $number = $base . '-' . $i++;
        }

        return $number;
    }

    /** Recalculate balance and status from total_amount / paid_amount (does not save). */
    public function refreshBalanceAndStatus(): void
    {
        $total = (float) $this->total_amount;
        $paid  = round((float) $this->paid_amount, 2);
        $balance = round($total - $paid, 2);

        $this->paid_amount = $paid;
        $this->balance_amount = $balance;
        $this->status = match (true) {
            $balance <= 0 => self::STATUS_PAID,
            $paid > 0     => self::STATUS_PARTIAL,
            default       => self::STATUS_PENDING,
        };
    }

    /** Bootstrap badge colour for the current status. */
    public function badgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PAID    => 'success',
            self::STATUS_PARTIAL => 'warning',
            default              => 'secondary',
        };
    }
}
