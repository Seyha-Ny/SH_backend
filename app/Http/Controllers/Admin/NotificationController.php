<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Latest notifications for the signed-in admin (JSON for the bell dropdown).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Unread count for the bell badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single notification as read (only the owner's own rows).
     */
    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json($notification);
    }

    /**
     * Mark every unread notification as read for the signed-in admin.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['updated' => $updated]);
    }
}
