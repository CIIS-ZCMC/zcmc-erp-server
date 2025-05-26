<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use App\Helpers\RealtimeCommunicationHelper;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a single user
     *
     * @param User $user The user to notify
     * @param array $notif_details Array containing notification details (title, description, module_path)
     * @return void
     */
    public function notify(User $user, array $notif_details): void
    {
        try {
            // Create the base notification record
            $notification = Notification::create([
                'title' => $notif_details['title'],
                'description' => $notif_details['description'],
                'module_path' => $notif_details['module_path'] ?? null,
            ]);

            // Create the user-specific notification link
            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'seen' => false
            ]);

            // Prepare notification data for socket emission
            $notificationData = [
                'id' => $notification->id,
                'title' => $notification->title,
                'description' => $notification->description,
                'module_path' => $notification->module_path,
                'created_at' => $notification->created_at,
                'user_id' => $user->id
            ];

            // Emit real-time notification through socket
            RealtimeCommunicationHelper::emitNotification($user->id, $notificationData);

            // If this is an AOP application notification, also emit an AOP update if applicable
            if (isset($notif_details['aop_application_id'])) {
                RealtimeCommunicationHelper::emitAopUpdate(
                    $notif_details['aop_application_id'],
                    [
                        'notification_id' => $notification->id,
                        'status' => $notif_details['status'] ?? 'updated',
                        'message' => $notification->description,
                        'timestamp' => now()->toIso8601String()
                    ]
                );
            }

            Log::info('Notification sent to user', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'title' => $notif_details['title']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a notification to multiple users
     *
     * @param array $users Array of User objects to notify
     * @param array $notif_details Array containing notification details (title, description, module_path)
     * @return void
     */
    public function notifyAll(array $users, array $notif_details): void
    {
        try {
            // Create the base notification record
            $notification = Notification::create([
                'title' => $notif_details['title'],
                'description' => $notif_details['description'],
                'module_path' => $notif_details['module_path'] ?? null,
            ]);

            // Collect user IDs for multi-user socket emission
            $userIds = [];

            // Create user-specific notification links for each user
            foreach ($users as $user) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'seen' => false
                ]);
                
                $userIds[] = $user->id;
            }

            // Prepare notification data for socket emission
            $notificationData = [
                'id' => $notification->id,
                'title' => $notification->title,
                'description' => $notification->description,
                'module_path' => $notification->module_path,
                'created_at' => $notification->created_at
            ];

            // Emit real-time notification to multiple users through socket
            RealtimeCommunicationHelper::emitMultiUserNotification($userIds, $notificationData);

            // If this is an AOP application notification, also emit an AOP update if applicable
            if (isset($notif_details['aop_application_id'])) {
                RealtimeCommunicationHelper::emitAopUpdate(
                    $notif_details['aop_application_id'],
                    [
                        'notification_id' => $notification->id,
                        'status' => $notif_details['status'] ?? 'updated',
                        'message' => $notification->description,
                        'timestamp' => now()->toIso8601String()
                    ]
                );
            }

            Log::info('Notification sent to multiple users', [
                'user_count' => count($users),
                'notification_id' => $notification->id,
                'title' => $notif_details['title']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notifications to multiple users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
