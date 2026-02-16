<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class ProviderDashboardService
{
    public function stats(User $provider): array
    {
        return [
            'bookings' => $this->bookingStats($provider),
            'finance'  => $this->financeStats($provider),
            'services' => $this->serviceStats($provider),
            'reviews'  => $this->reviewStats($provider),
            'charts'   => [
                'earnings_by_month' => $this->earningsByMonth($provider),
            ],
        ];
    }

    protected function bookingBaseQuery(User $provider)
    {
        return Booking::whereHas('service', function ($q) use ($provider) {
            $q->where('user_id', $provider->id);
        });
    }

    protected function bookingStats(User $provider): array
    {
        $query = $this->bookingBaseQuery($provider);

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        return [
            'total'       => $query->count(),
            'pending'     => (clone $query)->where('status', 'pending')->count(),
            'confirmed'   => (clone $query)->where('status', 'confirmed')->count(),
            'completed'   => (clone $query)->where('status', 'completed')->count(),
            'rejected'    => (clone $query)->where('status', 'cancelled')->count(),

            'this_month'  => (clone $query)
                ->whereBetween('created_at', [$startOfMonth, $now])
                ->count(),

            'last_month'  => (clone $query)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count(),
        ];
    }

    protected function financeStats(User $provider): array
    {
        $paidInvoices = Invoice::where('provider_id', $provider->id)
            ->where('status', 'paid');

        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        return [
            'total_earned' => (float) $paidInvoices->sum('total'),

            'this_year' => (float) (clone $paidInvoices)
                ->whereBetween('paid_at', [$startOfYear, $now])
                ->sum('total'),

            'this_month' => (float) (clone $paidInvoices)
                ->whereBetween('paid_at', [$startOfMonth, $now])
                ->sum('total'),

            'last_month' => (float) (clone $paidInvoices)
                ->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum('total'),

            'pending_amount' => (float) Invoice::where('provider_id', $provider->id)
                ->where('status', 'issued')
                ->sum('total'),
        ];
    }

    protected function serviceStats(User $provider): array
    {
        return [
            'total'  => Service::where('user_id', $provider->id)->count(),
            'active' => Service::where('user_id', $provider->id)
                ->where('is_active', true)
                ->count(),
        ];
    }

    protected function reviewStats(User $provider): array
    {
        return [
            'average_rating' => $provider->averageRating(),
            'reviews_count'  => $provider->ratingCount(),
        ];
    }

    protected function earningsByMonth(User $provider)
    {
        // in production DATE_FORMAT(paid_at, "%Y-%m") instead of 'strftime("%Y-%m", paid_at)
        return Invoice::selectRaw('strftime("%Y-%m", paid_at) as month, SUM(total) as total')
            ->where('provider_id', $provider->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
