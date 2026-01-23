<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_id',
        'booking_id',
        'type',
        'amount',
        'meta',
        'external_reference',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'processed_at' => 'datetime',
    ];

    /* ─────────────────────────────
     | RELATIONSHIPS
     ───────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /* ─────────────────────────────
     | DOMAIN HELPERS
     ───────────────────────────── */

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    public function isFee(): bool
    {
        return $this->type === 'fee';
    }

    public function isRefund(): bool
    {
        return $this->type === 'refund';
    }

    public function isPayout(): bool
    {
        return $this->type === 'payment_out';
    }

    public function isPaymentIn(): bool
    {
        return $this->type === 'payment_in';
    }
}
