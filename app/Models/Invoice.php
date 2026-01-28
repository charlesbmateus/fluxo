<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',       // client
        'provider_id',   // provider
        'booking_id',
        'subtotal',
        'fee',
        'tax',
        'total',
        'currency',
        'status',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'fee'       => 'decimal:2',
        'tax'       => 'decimal:2',
        'total'     => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at'   => 'datetime',
        'status'    => InvoiceStatus::class,
    ];

    /* ─────────────────────────────────────────
     |  RELATIONSHIPS
     ───────────────────────────────────────── */

    /**
     * Client who pays the invoice
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Provider who earns from the invoice
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Booking associated with the invoice
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Financial transactions linked to this invoice
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionLog::class);
    }

    /* ─────────────────────────────────────────
     |  STATUS HELPERS
     ───────────────────────────────────────── */

    public function isDraft(): bool
    {
        return $this->status === InvoiceStatus::DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === InvoiceStatus::ISSUED;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === InvoiceStatus::CANCELLED;
    }

    /* ─────────────────────────────────────────
     |  STATE TRANSITIONS
     ───────────────────────────────────────── */

    public function markAsIssued(): void
    {
        if (! $this->isDraft()) {
            return;
        }

        $this->update([
            'status'    => InvoiceStatus::ISSUED,
            'issued_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        // Inability to complete the consent form
        if ($this->status !== InvoiceStatus::ISSUED) {
            return;
        }

        $this->update([
            'status'  => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        // ❌ Paid invoices cannot be cancelled
        if ($this->status === InvoiceStatus::PAID) {
            return;
        }

        // ❌ Already cancelled → no-op
        if ($this->status === InvoiceStatus::CANCELLED) {
            return;
        }

        $this->update([
            'status' => InvoiceStatus::CANCELLED,
        ]);
    }

    /* ─────────────────────────────────────────
     |  FINANCIAL HELPERS
     ───────────────────────────────────────── */

    /**
     * Total amount provider should receive (after fees)
     */
    public function providerNetAmount(): float
    {
        return (float) ($this->subtotal - $this->fee);
    }

    /**
     * Total amount paid by the client
     */
    public function grossAmount(): float
    {
        return (float) $this->total;
    }

    /* ─────────────────────────────
 |  QUERY SCOPES
 ───────────────────────────── */

    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::PAID);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', InvoiceStatus::ISSUED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', InvoiceStatus::DRAFT);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', InvoiceStatus::CANCELLED);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('user_id', $clientId);
    }

    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
