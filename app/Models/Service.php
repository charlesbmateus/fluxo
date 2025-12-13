<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Class Service
 */
class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'pricing_model',
        'city',
        'country',
        'is_active',
        'thumbnail',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'status' => ServiceStatus::class,
    ];

    /* -----------------------------------------------------
     |  Eloquent relationships
     | ----------------------------------------------------- */

    public function provider(): BelongsTo
    {
        // Your migration used user_id for the owner of the service
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ServiceAvailability::class, 'service_id');
    }

    public function unAvailabilities(): HasMany
    {
        return $this->hasMany(ServiceUnavailability::class, 'service_id');
    }

    public function bookings(): HasMany
    {
        // If you used service_bookings table name / model Booking
        return $this->hasMany(Booking::class, 'service_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'service_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'service_id');
    }

    /* -----------------------------------------------------
     |  Model boot / events
     | ----------------------------------------------------- */

    protected static function booted(): void
    {
        // auto-generate slug on creating if empty
        static::creating(function (Service $service) {
            if (empty($service->slug) && ! empty($service->title)) {
                $service->slug = static::generateUniqueSlug($service->title);
            }
        });

        // keep slug up-to-date on title changes (optional)
        static::saving(function (Service $service) {
            if ($service->isDirty('title') && ! $service->isDirty('slug')) {
                $service->slug = static::generateUniqueSlug($service->title, $service->id);
            }
        });
    }

    /**
     * Generate a unique slug for the service.
     */
    public static function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearchTitleOrDescription($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = '%' . str_replace(' ', '%', trim($term)) . '%';

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('city', 'like', $term);
        });
    }

    /* -----------------------------------------------------
     |  Helpers
     | ----------------------------------------------------- */

    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating') ? (float) $this->reviews()->avg('rating') : null;
    }

    public function ratingCount(): int
    {
        return $this->reviews()->count();
    }

    public function formattedPrice(string $currency = 'CHF'): string
    {
        // basic formatting - you can replace with money library later
        return number_format((float) $this->price, 2) . ' ' . $currency;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }

        return asset('storage/' . $this->thumbnail);
    }

    /**
     * Basic availability check.
     *
     * Returns true if:
     * - There is no unavailability overlapping the requested period; AND
     * - There is at least one weekly availability slot for the requested day that contains the times.
     *
     * Note: This is a simple server-side check. For production you might want to generate discrete slots
     * (e.g. 30-min chunks) and check conflicts against bookings too.
     *
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return bool
     */
    public function isAvailableAt(\DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        // normalize to Carbon
        $startC = Carbon::instance($start)->setTimezone(config('app.timezone'));
        $endC = Carbon::instance($end)->setTimezone(config('app.timezone'));

        // 1) if any unavailability overlaps -> not available
        $overlapUnavail = $this->unAvailabilities()
            ->where(function ($q) use ($startC, $endC) {
                $q->whereBetween('start_datetime', [$startC->toDateTimeString(), $endC->toDateTimeString()])
                    ->orWhereBetween('end_datetime', [$startC->toDateTimeString(), $endC->toDateTimeString()])
                    ->orWhere(function ($q2) use ($startC, $endC) {
                        $q2->where('start_datetime', '<', $startC->toDateTimeString())
                            ->where('end_datetime', '>', $endC->toDateTimeString());
                    });
            })->exists();

        if ($overlapUnavail) {
            return false;
        }

        // 2) check weekly availability slots for the day of week
        // day_of_week in DB: 0 (Sunday) .. 6 (Saturday) — Carbon::dayOfWeek returns 0..6
        $dow = (int) $startC->dayOfWeek;
        $startTime = $startC->format('H:i:s');
        $endTime = $endC->format('H:i:s');

        $slotExists = $this->availabilities()
            ->where('day_of_week', $dow)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->where('is_active', true)
            ->exists();

        if (! $slotExists) {
            return false;
        }

        // 3) Also check existing bookings for collisions
        $collision = $this->bookings()
            ->where(function ($q) use ($startC, $endC) {
                $q->whereBetween('start_datetime', [$startC->toDateTimeString(), $endC->toDateTimeString()])
                    ->orWhereBetween('end_datetime', [$startC->toDateTimeString(), $endC->toDateTimeString()])
                    ->orWhere(function ($q2) use ($startC, $endC) {
                        $q2->where('start_datetime', '<', $startC->toDateTimeString())
                            ->where('end_datetime', '>', $endC->toDateTimeString());
                    });
            })->exists();

        return ! $collision;
    }
}
