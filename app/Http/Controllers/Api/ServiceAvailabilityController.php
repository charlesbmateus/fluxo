<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceAvailabilityController extends Controller
{
    /**
     * Check if a service is available between two datetimes
     * @throws \DateMalformedStringException
     */
    public function check(Request $request, Service $service)
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after:start'],
        ]);

        $available = $service->isAvailableAt(
            new \DateTime($validated['start']),
            new \DateTime($validated['end'])
        );

        return response()->json([
            'service_id' => $service->id,
            'available'  => $available,
        ]);
    }
}
