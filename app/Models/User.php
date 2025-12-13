<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * Attributes that can be mass assigned.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => Role::class,
    ];

    /* ─────────────────────────────────────────
     |  ROLE CHECKERS
     ───────────────────────────────────────── */

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isProvider(): bool
    {
        return $this->role === Role::PROVIDER;
    }

    public function isClient(): bool
    {
        return $this->role === Role::CLIENT;
    }

    /* ─────────────────────────────────────────
     |  RELATIONSHIPS
     ───────────────────────────────────────── */

    // Services that the provider offers
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    // Bookings made by the client
    public function clientBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    // Bookings assigned to a provider
    public function providerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    // Reviews received
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Payments done by the user
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Internal transaction logs
    public function transactionLogs(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }

    // Messages sent by the user
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /* ─────────────────────────────────────────
     |  PROFILE HELPERS
     ───────────────────────────────────────── */

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /* ─────────────────────────────────────────
     |  RATING HELPERS
     ───────────────────────────────────────── */

    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    public function ratingCount(): int
    {
        return $this->reviews()->count();
    }

    /* ─────────────────────────────────────────
     |  PROVIDER FINANCIAL HELPERS
     ───────────────────────────────────────── */

    public function totalEarnings(): float
    {
        return (float) $this->transactionLogs()
            ->where('type', 'payment_in')
            ->sum('amount');
    }

    public function balance(): float
    {
        return (float) $this->transactionLogs()->sum('amount');
    }

    /* ─────────────────────────────────────────
     |  BOOKING HELPERS
     ───────────────────────────────────────── */

    public function upcomingBookings(): HasMany
    {
        return $this->providerBookings()
            ->where('status', 'confirmed')
            ->where('start_time', '>', now());
    }

    public function pastBookings(): HasMany
    {
        return $this->providerBookings()
            ->where('status', 'completed');
    }
}
