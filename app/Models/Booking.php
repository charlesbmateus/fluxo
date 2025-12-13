<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',      // client
        'service_id',
        'scheduled_at',
        'status',
        'price',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'price'        => 'decimal:2',
    ];

    /**
     * Client who made the booking
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The service being booked
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * The provider of the service (through service)
     */
    public function provider(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Service::class,
            'id',       // Foreign key on Service table
            'id',       // Foreign key on User table
            'service_id', // Local key on Booking table
            'user_id'   // Local key on Service table
        );
    }

    /**
     * Scope to get only confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to get only upcoming bookings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now());
    }
}
