<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    public function show(): JsonResponse
    {
        $user = auth()->user();

        $data = [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role->value,
            'avatar' => $user->avatar_url,
        ];

        // Extra info for providers
        if ($user->isProvider()) {
            $data['metrics'] = [
                'total_earnings' => $user->totalEarnings(),
                'rating'         => $user->averageRating(),
                'reviews_count'  => $user->ratingCount(),
            ];
        }

        return ApiResponse::success($data);
    }
}
