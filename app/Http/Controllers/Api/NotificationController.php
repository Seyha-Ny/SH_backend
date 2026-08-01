<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user('sanctum')) {
            return response()->json([]);
        }
        $notifications = $request->user('sanctum')->notifications()
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        if (! $request->user('sanctum')) {
            return response()->json(['count' => 0]);
        }
        $count = $request->user('sanctum')->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        if (! $request->user('sanctum') || $notification->user_id !== $request->user('sanctum')->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }
}