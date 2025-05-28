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
        // Get user notifications
        $query = Notification::with(['userNotification']);

        // Filter by seen status if specified
        if ($request->has('seen') && $request->seen !== null) {
            $seen = filter_var($request->seen, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('userNotification', function($subquery) use ($seen) {
                $subquery->where('seen', $seen);
            });
        }

        // Order by creation date, the newest first
        $notifications = $query->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            'data' => NotificationResource::collection($notifications)
        ], Response::HTTP_OK);
    }

    public function getNotificationByStatus(bool $seen): JsonResponse
    {
        $notifications = Notification::with('user_notification')
            ->whereHas('user_notification', function ($query) use ($seen) {
                $query->where('seen', $seen);
            })
            ->orderBy('created_at', 'desc')
            ->get();


        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => NotificationResource::collection($notifications),
        ]);
    }

    public function employeeNotifications(Request $request): JsonResponse
    {
        $notifications = Notification::with('user_notification')
            ->whereHas('user_notification', function ($query) use ($profile_id) {
                $query->where('employee_profile_id', $profile_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();


        $data = NotificationResource::collection($notifications);

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => $data,
        ]);
    }

    public function markAsSeen(string $id): JsonResponse
    {
        $notifications = Notification::with('user_notification')->whereHas('user_notification', function ($query) {
            $query->where('seen', false);
        })->where('id', $id)->first();

        $notifications->user_notification->seen = true;
        $notifications->user_notification->save();

        $data = NotificationResource::make($notifications);

        return response()->json([
            'message' => 'Notification marked as seen successfully',
            'data' => $data,
        ]);
    }

    public function markAllAsSeen($profile_id): JsonResponse
    {
        // Fetch all notifications with unseen user notifications for the given profile
        $notifications = Notification::with('user_notification')
            ->whereHas('user_notification', function ($query) use ($profile_id) {
                $query->where('seen', false)
                    ->where('employee_profile_id', $profile_id);
            })
            ->get();

        // Check if there are any notifications to update
        if ($notifications->isEmpty()) {
            return response()->json([
                'message' => 'No unseen notifications found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Collect IDs of related user_notification records
        $userNotificationIds = $notifications->pluck('user_notification.id')->filter();

        // Bulk update user_notifications to mark them as seen
        UserNotification::whereIn('id', $userNotificationIds)
            ->update(['seen' => true]);

        // Transform the updated notifications
        $data = NotificationResource::collection($notifications);

        // Return success response
        return response()->json([
            'message' => 'All notifications marked as seen successfully',
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Notification $notification): JsonResponse
    {
        $data = NotificationResource::make($notification);
        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $data,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Notification $notification): JsonResponse
    {

        try {
            $notification->delete();
            return response()->json([
                'message' => 'Notification deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
