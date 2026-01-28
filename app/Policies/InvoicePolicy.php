<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id
            || $invoice->provider_id === $user->id;
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id
            && $invoice->status === InvoiceStatus::ISSUED;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id
            && $invoice->status !== InvoiceStatus::PAID;
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->provider_id
            && $invoice->status === InvoiceStatus::DRAFT;
    }
}
