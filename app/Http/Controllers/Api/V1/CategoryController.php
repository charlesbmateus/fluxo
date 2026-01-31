<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * List all categories
     *
     * GET /api/v1/categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'icon',
            ]);

        return ApiResponse::success($categories);
    }

    /**
     * Show a single category by slug
     *
     * GET /api/v1/categories/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return ApiResponse::success([
            'id'          => $category->id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'icon'        => $category->icon,
        ]);
    }
}
