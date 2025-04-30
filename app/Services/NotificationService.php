<?php

namespace App\Services;

use App\Models\AopApplication;
use App\Models\ApplicationTimeline;
use App\Models\AssignedArea;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create and send notifications for AOP application status changes
     * 
     * @param AopApplication $aopApplication The AOP application
     * @param ApplicationTimeline $timeline The newly created timeline entry
     * @param int $currentUserId The ID of the user who performed the action
     * @param int|null $nextAreaId The ID of the next assigned area in workflow (if any)
     * @param string $status The status of the application (approved, returned, etc)
     * @param string $stage The current stage in the workflow
     * @return void
     */
    public function sendAopStatusChangeNotifications(
        AopApplication $aopApplication, 
        ApplicationTimeline $timeline, 
        int $currentUserId, 
        ?int $nextAreaId, 
        string $status, 
        string $stage
    ): void
    {
        try {
            // Get the original requestor/creator of the AOP application
            $requestorId = $aopApplication->user_id;
            
            // 1. Create notification for the action that was just taken
            $actionNotification = new Notification([
                'title' => 'AOP Application ' . ucfirst($status),
                'description' => $this->getAopNotificationDescription($aopApplication, $status, $stage),
                'module_path' => '/aop-application/' . $aopApplication->id,
                'seen' => false,
                'user_id' => $currentUserId,  // Who created this notification
            ]);
            $actionNotification->save();
            
            // 2. Link notification to the original requestor (so they're always informed)
            if ($requestorId && $requestorId != $currentUserId) {
                UserNotification::create([
                    'seen' => false,
                    'notification_id' => $actionNotification->id,
                    'user_id' => $requestorId
                ]);
                
                Log::info('Sent notification to original requestor', [
                    'notification_id' => $actionNotification->id,
                    'user_id' => $requestorId,
                    'status' => $status
                ]);
            }
            
            // 3. If there's a next approver, also send notification to them
            if ($nextAreaId && $status === 'approved') {
                $nextArea = AssignedArea::with('user')->find($nextAreaId);
                
                if ($nextArea && $nextArea->user_id) {
                    // Create notification for next approver
                    $nextApproverNotification = new Notification([
                        'title' => 'AOP Application Requires Your Action',
                        'description' => "An AOP application is waiting for your review and action.",
                        'module_path' => '/aop-application/' . $aopApplication->id,
                        'seen' => false,
                        'user_id' => $currentUserId,  // Who created this notification
                    ]);
                    $nextApproverNotification->save();
                    
                    // Link to the next approver
                    UserNotification::create([
                        'seen' => false,
                        'notification_id' => $nextApproverNotification->id,
                        'user_id' => $nextArea->user_id
                    ]);
                    
                    Log::info('Sent notification to next approver', [
                        'notification_id' => $nextApproverNotification->id,
                        'next_area_id' => $nextAreaId,
                        'next_user_id' => $nextArea->user_id
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            // We don't want to fail the whole process if notifications fail, just log it
            Log::error('Error creating notifications: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Get the appropriate notification description based on status and stage for AOP applications
     * 
     * @param AopApplication $aopApplication The AOP application
     * @param string $status The status update (approved, returned, etc)
     * @param string $stage Current workflow stage
     * @return string The notification description
     */
    protected function getAopNotificationDescription(AopApplication $aopApplication, string $status, string $stage): string
    {
        if ($status === 'approved') {
            switch ($stage) {
                case 'planning_unit':
                    return "Your AOP application has been submitted and is now under review by the Planning Unit.";                    
                case 'division_chief':
                    return "Your AOP application has been approved by Planning Unit and forwarded to the Division Chief for review.";                    
                case 'omcc':
                    return "Your AOP application has been approved by the Division Chief and forwarded to the Medical Center Chief for final approval.";                    
                case 'final':
                    return "Your AOP application has been fully approved by the Medical Center Chief. Congratulations!";                    
                default:
                    return "Your AOP application has been approved and moved to the next stage of review.";                    
            }
        } elseif ($status === 'returned') {
            return "Your AOP application has been returned for revisions. Please check the remarks for details.";            
        } else {
            return "Your AOP application status has been updated to: " . ucfirst($status);
        }
    }
    
    /**
     * Create and send a general notification to a specific user
     * 
     * @param int $userId The recipient user ID
     * @param string $title The notification title
     * @param string $description The notification description
     * @param string $modulePath The module path (URL) for this notification
     * @param int $creatorId The user ID who created this notification
     * @return Notification|null The created notification or null on failure
     */
    public function sendUserNotification(
        int $userId, 
        string $title, 
        string $description, 
        string $modulePath = '', 
        int $creatorId = 0
    ): ?Notification
    {
        try {
            // If creator not specified, use recipient as creator
            if ($creatorId === 0) {
                $creatorId = $userId;
            }
            
            // Create the notification
            $notification = new Notification([
                'title' => $title,
                'description' => $description,
                'module_path' => $modulePath,
                'seen' => false,
                'user_id' => $creatorId
            ]);
            $notification->save();
            
            // Link to the recipient
            UserNotification::create([
                'seen' => false,
                'notification_id' => $notification->id,
                'user_id' => $userId
            ]);
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Error sending user notification: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $userId,
                'title' => $title
            ]);
            
            return null;
        }
    }
    
    /**
     * Send a notification to multiple users
     * 
     * @param array $userIds Array of recipient user IDs
     * @param string $title The notification title
     * @param string $description The notification description
     * @param string $modulePath The module path (URL) for this notification
     * @param int $creatorId The user ID who created this notification
     * @return Notification|null The created notification or null on failure
     */
    public function sendMultiUserNotification(
        array $userIds, 
        string $title, 
        string $description, 
        string $modulePath = '', 
        int $creatorId = 0
    ): ?Notification
    {
        try {
            // If creator not specified and we have recipients, use first recipient as creator
            if ($creatorId === 0 && !empty($userIds)) {
                $creatorId = $userIds[0];
            }
            
            // Create one notification
            $notification = new Notification([
                'title' => $title,
                'description' => $description,
                'module_path' => $modulePath,
                'seen' => false,
                'user_id' => $creatorId
            ]);
            $notification->save();
            
            // Link to all recipients
            foreach ($userIds as $userId) {
                UserNotification::create([
                    'seen' => false,
                    'notification_id' => $notification->id,
                    'user_id' => $userId
                ]);
            }
            
            return $notification;
        } catch (\Exception $e) {
            Log::error('Error sending multi-user notification: ' . $e->getMessage(), [
                'exception' => $e,
                'user_count' => count($userIds),
                'title' => $title
            ]);
            
            return null;
        }
    }
}
