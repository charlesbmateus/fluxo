<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',     // client who leaves the review
        'service_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /* -----------------------------------------------------
     |  Relationships
     | ----------------------------------------------------- */

    /**
     * Client who wrote the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reviewed service
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Provider of the reviewed service (through service)
     */
    public function provider(): BelongsTo
    {
        return $this->service->user();
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->whereHas('service', function ($q) use ($providerId) {
            $q->where('user_id', $providerId);
        });
    }

    public function scopeWithRatingAtLeast($query, int $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /* -----------------------------------------------------
     |  Helpers
     | ----------------------------------------------------- */

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }
}
