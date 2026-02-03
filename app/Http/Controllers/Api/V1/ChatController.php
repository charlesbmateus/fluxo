<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use AuthorizesRequests;
    /**
     * List conversations for authenticated user
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $conversations = Conversation::where('client_id', $user->id)
            ->orWhere('provider_id', $user->id)
            ->with(['booking', 'messages.sender'])
            ->latest()
            ->get();

        return ApiResponse::success($conversations);
    }

    /**
     * Show messages in a conversation
     */
    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        return ApiResponse::success(
            $conversation->load('messages.sender')
        );
    }

    /**
     * Send a message
     */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('sendMessage', $conversation);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => auth()->id(),
            'content'         => $data['content'],
        ]);

        return ApiResponse::success(
            $message->load('sender'),
            'Message sent',
            201
        );
    }

    /**
     * Mark all messages as read
     */
    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', auth()->id())
            ->update(['read_at' => now()]);

        return ApiResponse::success(null, 'Messages marked as read');
    }
}
