<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',          // client paying
        'booking_id',
        'amount',
        'currency',
        'status',
        'platform_fee',
        'payment_method',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'platform_fee'  => 'decimal:2',
        'status'        => PaymentStatus::class,
    ];

    /* -----------------------------------------------------
     |  Relationships
     | ----------------------------------------------------- */

    /**
     * Client who made the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Booking being paid
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Invoice generated from this payment
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Financial ledger entries
     */
    public function transactions()
    {
        return $this->hasMany(TransactionLog::class);
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED->value);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING->value);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->whereHas('booking.service', function ($q) use ($providerId) {
            $q->where('user_id', $providerId);
        });
    }

    /* -----------------------------------------------------
     |  Helpers
     | ----------------------------------------------------- */

    public function netAmount(): float
    {
        return (float) $this->amount - (float) $this->platform_fee;
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }
}
