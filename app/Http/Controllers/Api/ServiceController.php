<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * List services with filters
     */
    public function index(Request $request)
    {
        $services = Service::query()
            ->with(['category', 'user'])
            ->active()
            ->when($request->category, fn ($q) =>
            $q->byCategory($request->category)
            )
            ->when($request->city, fn ($q) =>
            $q->where('city', 'like', '%' . $request->city . '%')
            )
            ->when($request->search, fn ($q) =>
            $q->searchTitleOrDescription($request->search)
            )
            ->latest()
            ->paginate(10);

        return response()->json($services);
    }

    /**
     * Show a single service
     */
    public function show(Service $service)
    {
        $service->load([
            'category',
            'user',
            'availabilities',
            'reviews',
        ]);

        return response()->json($service);
    }
}
