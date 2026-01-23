<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'read',
        'sent_at',
    ];

    protected $casts = [
        'read'    => 'boolean',
        'sent_at'=> 'datetime',
    ];

    /* -----------------------------------------------------
     |  Relationships
     | ----------------------------------------------------- */

    /**
     * User who receives the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    /**
     * Only unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Only read notifications
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Filter by notification type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /* -----------------------------------------------------
     |  Helpers
     | ----------------------------------------------------- */

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (! $this->read) {
            $this->update(['read' => true]);
        }
    }

    /**
     * Mark notification as sent (useful for email / push)
     */
    public function markAsSent(): void
    {
        if (! $this->sent_at) {
            $this->update(['sent_at' => now()]);
        }
    }

    // Notifications received by user
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
