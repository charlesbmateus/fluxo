<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ServiceAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ─────────────────────────────────────────
     | RELATIONSHIPS
     ───────────────────────────────────────── */

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /* ─────────────────────────────────────────
     | QUERY SCOPES
     ───────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /* ─────────────────────────────────────────
     | DOMAIN HELPERS
     ───────────────────────────────────────── */

    public function coversTime(string $time): bool
    {
        return $time >= $this->start_time && $time <= $this->end_time;
    }

    public function coversDateTime(\DateTimeInterface $dateTime): bool
    {
        return
            (int) $dateTime->format('w') === $this->day_of_week
            && $this->coversTime($dateTime->format('H:i:s'));
    }

    public function durationInMinutes(): int
    {
        return Carbon::parse($this->start_time)
            ->diffInMinutes(Carbon::parse($this->end_time));
    }
}
