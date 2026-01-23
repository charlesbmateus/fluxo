<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'booking_id',
        'content',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /* -----------------------------------------------------
     |  Relationships
     | ----------------------------------------------------- */

    /**
     * User who sent the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * User who receives the message
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Optional booking context (chat tied to a service booking)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /* -----------------------------------------------------
     |  Scopes
     | ----------------------------------------------------- */

    /**
     * Only unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Messages between two users (conversation)
     */
    public function scopeBetweenUsers($query, int $userA, int $userB)
    {
        return $query->where(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)
                ->where('receiver_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)
                ->where('receiver_id', $userA);
        });
    }

    /**
     * Messages for a specific booking
     */
    public function scopeForBooking($query, int $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    /* -----------------------------------------------------
     |  Helpers
     | ----------------------------------------------------- */

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    // Messages sent by user
    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    // Messages received by user
    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    public function allMessages()
    {
        return ChatMessage::where('sender_id', $this->id)
            ->orWhere('receiver_id', $this->id);
    }
}
