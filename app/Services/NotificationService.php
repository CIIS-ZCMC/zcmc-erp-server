<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\UserNotification;
use App\Helpers\RealtimeCommunicationHelper;
use App\Http\Resources\NotificationResource;
use App\Jobs\EmitNewDataToSocketConnectionJob;
use Illuminate\Support\Facades\Log;
use App\Services\EmailService;

class NotificationService
{
    protected EmailService $emailService;

    /**
     * Create a new service instance.
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

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

            // Load the userNotification relationship (camelCase to match the model method)
            $notification->load('userNotification');

            // Use NotificationResource to format data consistently
            $notification_data = (new NotificationResource($notification))->toArray(request());

            // Add user_id for socket emission purposes
            $notification_data['user_id'] = $user->id;

            // Dispatch job for real-time notification through socket
            EmitNewDataToSocketConnectionJob::dispatch(
                RealtimeCommunicationHelper::$NOTIFICATION_EVENT . '-' . $user->id,
                $notification_data
            );

            // Send email notification if user has an email
            if ($user->email) {
                // Prepare email data
                $emailData = [
                    'subject' => $notif_details['title'],
                    'message' => $notif_details['description'],
                    'status' => $notif_details['status'] ?? 'updated',
                    'module_path' => $notif_details['module_path'] ?? null,
                ];

                // Add AOP application ID if present
                if (isset($notif_details['aop_application_id'])) {
                    $emailData['aop_application_id'] = $notif_details['aop_application_id'];

                    // Include remarks in email data if present (for returned applications)
                    if (isset($notif_details['remarks']) && !empty($notif_details['remarks'])) {
                        $emailData['remarks'] = $notif_details['remarks'];
                    }

                    // Add area information to the email data if present
                    if (isset($notif_details['current_area'])) {
                        $emailData['current_area'] = $notif_details['current_area'];
                    }

                    if (isset($notif_details['next_area'])) {
                        $emailData['next_area'] = $notif_details['next_area'];
                    }

                    if (isset($notif_details['stage'])) {
                        $emailData['stage'] = $notif_details['stage'];
                    }

                    // Determine context based on notification purpose
                    $context = 'update_user';
                    if (str_contains($notif_details['title'], 'Requires Your Action')) {
                        $context = 'update_next_approver';
                    } else if ($notif_details['status'] === 'returned') {
                        $context = 'returned_application';
                    } else if ($notif_details['status'] === 'approved' && isset($notif_details['next_area']) && $notif_details['next_area'] === null) {
                        $context = 'final_approval';
                    }

                    // Send AOP-specific email notification with context
                    $this->emailService->sendAopStatusUpdate('dev.artlouises@gmail.com', $context, $emailData);
                } else {
                    // Send general notification email
                    $this->emailService->sendNotification('dev.artlouises@gmail.com', $emailData);
                }
            }

            // If this is an AOP application notification, also emit an AOP update if applicable
            if (isset($notif_details['aop_application_id'])) {
                $aopUpdateData = [
                    'notification_id' => $notification->id,
                    'status' => $notif_details['status'] ?? 'updated',
                    'message' => $notification->description,
                    'timestamp' => now()->toIso8601String()
                ];

                // Dispatch job for AOP update
                EmitNewDataToSocketConnectionJob::dispatch(
                    RealtimeCommunicationHelper::$AOP_UPDATE_EVENT . '-' . $user->id,
                    $aopUpdateData
                );
            }

            Log::info('Notification sent to user', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'title' => $notif_details['title'],
                'email_sent' => !empty($user->email)
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
            $emailRecipients = [];

            // Create user-specific notification links for each user
            foreach ($users as $user) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'seen' => false
                ]);

                $userIds[] = $user->id;

                // Collect email addresses for bulk email
                if ($user->email) {
                    $emailRecipients[] = $user->email;
                }

                // Load the userNotification relationship for consistent resource creation
                $notification->load('userNotification');

                // Dispatch individual notification jobs for each user
                $userData = (new NotificationResource($notification))->toArray(request());
                $userData['user_id'] = $user->id;

                EmitNewDataToSocketConnectionJob::dispatch(
                    RealtimeCommunicationHelper::$NOTIFICATION_EVENT . '-' . $user->id,
                    $userData
                );
            }

            // Send email notifications if we have recipients
            if (!empty($emailRecipients)) {
                // Prepare email data
                $emailData = [
                    'subject' => $notif_details['title'],
                    'message' => $notif_details['description'],
                    'status' => $notif_details['status'] ?? 'updated',
                    'module_path' => $notif_details['module_path'] ?? null,
                ];

                // Add AOP application ID if present
                if (isset($notif_details['aop_application_id'])) {
                    $emailData['aop_application_id'] = $notif_details['aop_application_id'];

                    // Add area information to the email data if present
                    if (isset($notif_details['current_area'])) {
                        $emailData['current_area'] = $notif_details['current_area'];
                    }

                    if (isset($notif_details['next_area'])) {
                        $emailData['next_area'] = $notif_details['next_area'];
                    }

                    if (isset($notif_details['stage'])) {
                        $emailData['stage'] = $notif_details['stage'];
                    }

                    // For bulk notifications, use general update context
                    $context = 'update_all';

                    // Send individual AOP-specific emails to maintain personalization
                    foreach ($emailRecipients as $recipient) {
                        $this->emailService->sendAopStatusUpdate($recipient, $context, $emailData);
                    }
                } else {
                    // Send general bulk notification email
                    $this->emailService->sendBulkNotification($emailRecipients, $emailData);
                }
            }

            // If this is an AOP application notification, also emit an AOP update if applicable
            if (isset($notif_details['aop_application_id'])) {
                $aopUpdateData = [
                    'notification_id' => $notification->id,
                    'status' => $notif_details['status'] ?? 'updated',
                    'message' => $notification->description,
                    'timestamp' => now()->toIso8601String()
                ];

                // Dispatch job for AOP update
                EmitNewDataToSocketConnectionJob::dispatch(
                    RealtimeCommunicationHelper::$AOP_UPDATE_EVENT . '-' . $notif_details['aop_application_id'],
                    $aopUpdateData
                );
            }

            Log::info('Notification sent to multiple users', [
                'user_count' => count($users),
                'email_recipient_count' => count($emailRecipients),
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
