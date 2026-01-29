<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get();

        return ApiResponse::success(
            $notifications,
            'Notifications retrieved successfully'
        );
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // 🔐 Authorization: only owner
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update([
            'read' => true,
        ]);

        return ApiResponse::success(
            $notification->refresh(),
            'Notification marked as read'
        );
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = auth()->user()
            ->notifications()
            ->where('read', false)
            ->update(['read' => true]);

        return ApiResponse::success(
            ['updated' => $count],
            'All notifications marked as read'
        );
    }
}
