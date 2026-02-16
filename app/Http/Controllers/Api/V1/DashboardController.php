<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function provider(): JsonResponse
    {
        $provider = auth()->user();

        // ───────── BOOKINGS ─────────

        $bookingsQuery = Booking::whereHas('service', function ($q) use ($provider) {
            $q->where('user_id', $provider->id);
        });

        $bookings = [
            'total'      => $bookingsQuery->count(),
            'pending'    => (clone $bookingsQuery)->where('status', 'pending')->count(),
            'confirmed'  => (clone $bookingsQuery)->where('status', 'confirmed')->count(),
            'completed'  => (clone $bookingsQuery)->where('status', 'completed')->count(),
            'cancelled'  => (clone $bookingsQuery)->where('status', 'cancelled')->count(),
            'this_month' => (clone $bookingsQuery)->whereMonth('created_at', now()->month)->count(),
            'last_month' => (clone $bookingsQuery)->whereMonth('created_at', now()->subMonth()->month)->count(),
        ];

        // ───────── BOOKINGS CHART (12 months) ─────────

        $monthlyBookings = Booking::selectRaw('strftime("%Y-%m", created_at) as month, COUNT(*) as total')
            ->whereHas('service', function ($q) use ($provider) {
                $q->where('user_id', $provider->id);
            })
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ───────── FINANCE ─────────

        $paidInvoices = Invoice::where('provider_id', $provider->id)
            ->where('status', 'paid');

        $finance = [
            'total_earned' => $paidInvoices->sum('total'),
            'this_month'   => (clone $paidInvoices)->whereMonth('paid_at', now()->month)->sum('total'),
            'last_month'   => (clone $paidInvoices)->whereMonth('paid_at', now()->subMonth()->month)->sum('total'),
            'yearly_total' => (clone $paidInvoices)
                ->where('paid_at', '>=', now()->subMonths(12))
                ->sum('total'),
            'pending' => Invoice::where('provider_id', $provider->id)
                ->where('status', 'issued')
                ->sum('total'),
        ];

        // ───────── CHART DATA ─────────

        $rawRevenue = Invoice::selectRaw('strftime("%Y-%m", paid_at) as month, SUM(total) as total')
            ->where('provider_id', $provider->id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Generate last 12 months
        $monthlyRevenue = collect();

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');

            $monthlyRevenue->push([
                'month' => $month,
                'total' => isset($rawRevenue[$month]) ? (float) $rawRevenue[$month] : 0,
            ]);
        }

        // ───────── SERVICES ─────────

        $servicesQuery = Service::where('user_id', $provider->id);

        $services = [
            'total'    => $servicesQuery->count(),
            'active'   => (clone $servicesQuery)->where('is_active', true)->count(),
            'inactive' => (clone $servicesQuery)->where('is_active', false)->count(),
        ];

        // ───────── REVIEWS ─────────

        $reviews = [
            'average_rating' => round($provider->averageRating(), 1),
            'reviews_count'  => $provider->ratingCount(),
        ];

        return ApiResponse::success([
            'bookings' => array_merge($bookings, [
                'monthly_chart' => $monthlyBookings,
            ]),
            'finance'  => array_merge($finance, [
                'monthly_chart' => $monthlyRevenue,
            ]),
            'services' => $services,
            'reviews'  => $reviews,
        ]);
    }
}
