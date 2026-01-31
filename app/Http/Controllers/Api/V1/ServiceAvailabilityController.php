<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceAvailabilityController extends Controller
{
    /**
     * Check if a service is available between two datetimes
     *
     * GET /api/v1/services/{service}/availability/check
     */
    public function check(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after:start'],
        ]);

        $available = $service->isAvailableAt(
            new \DateTime($validated['start']),
            new \DateTime($validated['end'])
        );

        return ApiResponse::success([
            'service_id' => $service->id,
            'start'      => $validated['start'],
            'end'        => $validated['end'],
            'available'  => $available,
        ]);
    }
}
