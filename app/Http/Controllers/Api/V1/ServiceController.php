<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * List services (public)
     * Supports filters: category, price, provider
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()
            ->with(['category', 'provider'])
            ->where('is_active', true);

        // 🔎 Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('provider_id')) {
            $query->where('user_id', $request->provider_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $services = $query->latest()->paginate(12);

        return ApiResponse::success($services);
    }

    /**
     * Show service details
     */
    public function show(Service $service): JsonResponse
    {
        return ApiResponse::success(
            $service->load([
                'category',
                'provider',
                'reviews.user',
            ])
        );
    }

    /**
     * Get service availability for a given date
     * ?date=2026-02-15
     */
    public function availability(Service $service, Request $request): JsonResponse
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        $dayOfWeek = $date->dayOfWeek;

        // Base availability
        $availabilities = $service->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        // Existing bookings
        $bookedSlots = $service->bookings()
            ->whereDate('start_datetime', $date->toDateString())
            ->get(['start_datetime', 'end_datetime']);

        return ApiResponse::success([
            'date'          => $date->toDateString(),
            'availabilities'=> $availabilities,
            'booked_slots'  => $bookedSlots,
        ]);
    }

    /**
     * List services by provider
     */
    public function byProvider(User $provider): JsonResponse
    {
        if (! $provider->isProvider()) {
            abort(404);
        }

        $services = Service::where('user_id', $provider->id)
            ->where('is_active', true)
            ->with('category')
            ->get();

        return ApiResponse::success($services);
    }
}
