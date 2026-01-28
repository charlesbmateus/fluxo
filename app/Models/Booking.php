<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'start_datetime',
        'end_datetime',
        'status',
        'price',
        'notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'price'          => 'decimal:2',
        'status'         => BookingStatus::class,
    ];

    /* ─────────────────────────────────────────
     |  RELATIONSHIPS
     ───────────────────────────────────────── */

    // Client who made the booking
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Service being booked
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Provider (through service)
    public function provider(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Service::class,
            'id',           // Service.id
            'id',           // User.id
            'service_id',   // Booking.service_id
            'user_id'       // Service.user_id
        );
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /* ─────────────────────────────────────────
     |  SCOPES
     ───────────────────────────────────────── */

    public function scopeConfirmed($query)
    {
        return $query->where('status', BookingStatus::CONFIRMED->value);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_datetime', '<', now());
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->whereHas('service', function ($q) use ($providerId) {
            $q->where('user_id', $providerId);
        });
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            BookingStatus::PENDING->value,
            BookingStatus::CONFIRMED->value,
        ]);
    }

    /* ─────────────────────────────────────────
     |  HELPERS
     ───────────────────────────────────────── */

    public function isPending(): bool
    {
        return $this->status === BookingStatus::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::CANCELLED;
    }
}
