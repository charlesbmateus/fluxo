<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUnavailability extends Model
{
    protected $fillable = [
        'service_id',
        'start_datetime',
        'end_datetime',
        'reason',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];

    /* ─────────────────────────────────────────
     |  RELATIONSHIPS
     ───────────────────────────────────────── */

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /* ─────────────────────────────────────────
     |  HELPERS
     ───────────────────────────────────────── */

    public function overlaps(\DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        return $this->start_datetime < $end
            && $this->end_datetime > $start;
    }
}
