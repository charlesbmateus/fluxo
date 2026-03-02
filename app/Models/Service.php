<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

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
        'status',
        'thumbnail',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
        'status'    => ServiceStatus::class,
    ];

    protected $with = ['primaryImage'];

    /* ─────────────────────────────────────────
     |  RELATIONSHIPS
     ───────────────────────────────────────── */

    /**
     * Provider who offers the service
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Provider (owner)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Weekly availability (Mon–Sun slots)
    public function availabilities(): HasMany
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    // Temporary blocks (vacation, sick leave, etc.)
    public function unavailabilities(): HasMany
    {
        return $this->hasMany(ServiceUnavailability::class);
    }

    // Bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Reviews
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ServiceImage::class)->where('is_primary', true);
    }

    /* ─────────────────────────────────────────
     |  MODEL EVENTS
     ───────────────────────────────────────── */

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (empty($service->slug)) {
                $service->slug = static::generateUniqueSlug($service->title);
            }
        });

        static::updating(function (Service $service) {
            if ($service->isDirty('title')) {
                $service->slug = static::generateUniqueSlug($service->title, $service->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
        static::where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* ─────────────────────────────────────────
     |  SCOPES
     ───────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $term = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('city', 'like', $term);
        });
    }

    /* ─────────────────────────────────────────
     |  AVAILABILITY LOGIC
     ───────────────────────────────────────── */

    public function hasWeeklyAvailabilityFor(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): bool {
        $startC = Carbon::instance($start);
        $endC   = Carbon::instance($end);

        return $this->availabilities()
            ->where('is_active', true)
            ->where('day_of_week', $startC->dayOfWeek)
            ->where('start_time', '<=', $startC->format('H:i:s'))
            ->where('end_time', '>=', $endC->format('H:i:s'))
            ->exists();
    }

    public function hasUnavailabilityOverlap(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): bool {
        return $this->unavailabilities()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_datetime', '<', $start)
                            ->where('end_datetime', '>', $end);
                    });
            })
            ->exists();
    }

    public function hasBookingOverlap(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): bool {
        return $this->bookings()
            ->whereBetween('start_datetime', [$start, $end])
            ->exists();
    }

    public function isAvailableAt(
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): bool {
        if ($this->hasUnavailabilityOverlap($start, $end)) {
            return false;
        }

        if (! $this->hasWeeklyAvailabilityFor($start, $end)) {
            return false;
        }

        if ($this->hasBookingOverlap($start, $end)) {
            return false;
        }

        return true;
    }

    /* ─────────────────────────────────────────
     |  HELPERS
     ───────────────────────────────────────── */

    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    public function ratingCount(): int
    {
        return $this->reviews()->count();
    }

    public function formattedPrice(string $currency = 'CHF'): string
    {
        return number_format((float) $this->price, 2) . ' ' . $currency;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : null;
    }
}
