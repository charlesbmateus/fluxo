<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function pay(Invoice $invoice, User $actor): Invoice
    {
        // 1️⃣ Autorización: solo el cliente puede pagar
        if ($invoice->user_id !== $actor->id) {
            throw ValidationException::withMessages([
                'invoice' => 'You are not allowed to pay this invoice.',
            ]);
        }

        // 2️⃣ Estado válido
        if (! in_array($invoice->status, [
            InvoiceStatus::ISSUED,
            InvoiceStatus::DRAFT,
        ])) {
            throw ValidationException::withMessages([
                'invoice' => 'This invoice cannot be paid.',
            ]);
        }

        // 3️⃣ Marcar como pagada
        $invoice->update([
            'status'  => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);

        // 4️⃣ 🔔 NOTIFICACIÓN (ESTO ES LO QUE FALTABA)
        $this->notificationService->notify(
            user: $invoice->provider,
            type: 'invoice_paid',
            message: 'An invoice has been paid.'
        );

        return $invoice;
    }

    public function createFromBooking(Booking $booking): Invoice
    {
        // 1️⃣ Validación de estado
        if ($booking->status !== BookingStatus::COMPLETED) {
            throw ValidationException::withMessages([
                'booking' => 'Only completed bookings can be invoiced.',
            ]);
        }

        // 2️⃣ Evitar duplicados
        if ($booking->invoice()->exists()) {
            throw ValidationException::withMessages([
                'booking' => 'This booking is already invoiced.',
            ]);
        }

        // 3️⃣ Cálculos
        $subtotal = $booking->price;
        $fee      = round($subtotal * 0.10, 2);
        $tax      = 0;
        $total    = $subtotal + $fee + $tax;

        // 4️⃣ CREACIÓN CORRECTA
        return Invoice::create([
            'number'      => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),

            // 🔥 ESTA ERA LA LÍNEA QUE FALTABA
            'user_id'     => $booking->user_id,               // cliente

            'provider_id' => $booking->service->user_id,      // provider
            'booking_id'  => $booking->id,

            'subtotal'    => $subtotal,
            'fee'         => $fee,
            'tax'         => $tax,
            'total'       => $total,

            'currency'    => 'CHF',
            'status'      => InvoiceStatus::DRAFT,
        ]);
    }
}
