<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\UserNotification;
use App\Http\Resources\NotificationResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Get user notifications
        $query = Notification::whereHas('userNotification', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['userNotification' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        // Filter by seen status if specified
        if ($request->has('seen') && $request->seen !== null) {
            $seen = filter_var($request->seen, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('userNotification', function($subquery) use ($user, $seen) {
                $subquery->where('user_id', $user->id)
                         ->where('seen', $seen);
            });
        }

        // Order by creation date, the newest first
        $notifications = $query->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            'data' => NotificationResource::collection($notifications)
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Note: This is typically handled by the NotificationService
     * and not directly by the controller in most cases.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'module_path' => 'nullable|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        // Create the notification
        $notification = Notification::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'module_path' => $validated['module_path'] ?? null,
        ]);

        // Create user notifications for each specified user
        foreach ($validated['user_ids'] as $userId) {
            UserNotification::create([
                'user_id' => $userId,
                'notification_id' => $notification->id,
                'seen' => false
            ]);
        }

        // Load the userNotification relationship
        $notification->load('userNotification');

        return response()->json([

        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return NotificationResource|JsonResponse
     */
    public function show(string $id): NotificationResource|JsonResponse
    {
        $user = Auth::user();

        $notification = Notification::with(['userNotification' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->whereHas('userNotification', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json([
            'message' => 'Notification retrieved successfully',
            'data' => new NotificationResource($notification)
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return NotificationResource|JsonResponse
     */
    public function update(Request $request, string $id): NotificationResource|JsonResponse
    {
        $user = Auth::user();

        // Find the notification
        $notification = Notification::whereHas('userNotification', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // Update the seen status
        if ($request->has('seen')) {
            $seen = filter_var($request->seen, FILTER_VALIDATE_BOOLEAN);

            UserNotification::where('notification_id', $notification->id)
                            ->where('user_id', $user->id)
                            ->update(['seen' => $seen]);

            // Reload the relation to reflect changes
            $notification->load(['userNotification' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }]);
        }

        return response()->json([
            'message' => 'Notification updated successfully',
            'data' => new NotificationResource($notification)
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();

        // Find the user notification record
        $userNotification = UserNotification::where('notification_id', $id)
                                           ->where('user_id', $user->id)
                                           ->first();

        if (!$userNotification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // Delete the user notification (not the base notification)
        $userNotification->delete();

        return response()->json(['message' => 'Notification removed successfully']);
    }

    /**
     * Mark all notifications as seen for the authenticated user.
     *
     * @return JsonResponse
     */
    public function markAllAsSeen(): JsonResponse
    {
        $user = Auth::user();

        UserNotification::where('user_id', $user->id)
                        ->where('seen', false)
                        ->update(['seen' => true]);

        return response()->json(['message' => 'All notifications marked as seen']);
    }

    /**
     * Get the count of unseen notifications for the authenticated user.
     *
     * @return JsonResponse
     */
    public function getUnseenCount(): JsonResponse
    {
        $user = Auth::user();

        $count = UserNotification::where('user_id', $user->id)
                                ->where('seen', false)
                                ->count();

        return response()->json(['unseen_count' => $count]);
    }
}
