<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
