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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'stripe_account_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => Role::class,
    ];

    /* ─────────────────────────────
     | ROLES
     ───────────────────────────── */

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

    /* ─────────────────────────────
     | RELATIONSHIPS
     ───────────────────────────── */

    // Provider → services
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // Client → bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    // Client → reviews written
    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    // Provider → reviews received (via services)
    public function reviewsReceived()
    {
        return Review::whereIn(
            'service_id',
            $this->services()->pluck('id')
        );
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    // Payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function transactionLogs(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }

    // Chat
    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    /* ─────────────────────────────
     | ACCESSORS
     ───────────────────────────── */

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /* ─────────────────────────────
     | RATINGS
     ───────────────────────────── */

    public function averageRating(): ?float
    {
        return round($this->reviewsReceived()->avg('rating'), 2);
    }

    public function ratingCount(): int
    {
        return $this->reviewsReceived()->count();
    }

    /* ─────────────────────────────
     | PROVIDER BOOKINGS (via services)
     ───────────────────────────── */

    public function providerBookings()
    {
        return Booking::whereIn(
            'service_id',
            $this->services()->pluck('id')
        );
    }

    public function upcomingBookings()
    {
        return $this->providerBookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '>', now());
    }

    public function pastBookings()
    {
        return $this->providerBookings()
            ->where('status', 'completed');
    }

    /* ─────────────────────────────
     | FINANCE
     ───────────────────────────── */

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
}
