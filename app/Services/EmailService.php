<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\AopApplication;
use App\Models\User;
use App\Models\AssignedArea;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class EmailService
{
    /**
     * Send an email with the provided data
     * 
     * @param string $recipientEmail The email address of the recipient
     * @param array $emailData The data to be passed to the email template
     * @return bool Whether the email was queued successfully
     */
    public function sendEmail(string $recipientEmail, array $emailData): bool
    {
        try {
            // Dispatch to queue
            dispatch(new SendEmailJob($recipientEmail, $emailData));
            
            Log::info('Email notification queued', [
                'recipient' => $recipientEmail,
                'subject' => $emailData['subject'] ?? 'No Subject',
                'context' => $emailData['context'] ?? 'general'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending email notification: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'recipient' => $recipientEmail
            ]);
            
            return false;
        }
    }

    /**
     * Send an approval workflow email notification
     * 
     * @param AopApplication $aopApplication The AOP application
     * @param int $recipientUserId The user ID of the email recipient
     * @param int $actionUserId The user ID who performed the action
     * @param string $title The email subject
     * @param string $message The email message/body
     * @return bool Whether the email was queued successfully
     */
    public function sendApprovalEmail(
        AopApplication $aopApplication,
        int $recipientUserId,
        int $actionUserId,
        string $title,
        string $message
    ): bool {
        try {
            // Load the recipient user with their assigned area
            $recipient = User::with('assignedArea')->find($recipientUserId);
            if (!$recipient) {
                Log::warning('Failed to send email notification: Recipient user not found', [
                    'recipient_id' => $recipientUserId,
                    'aop_id' => $aopApplication->id
                ]);
                return false;
            }
            
            // Load the action user with their assigned area
            $actionUser = User::with('assignedArea')->find($actionUserId);
            
            // Initialize next office variables
            $nextArea = null;
            $nextOfficeAreaName = 'N/A';
            $nextOfficeAreaCode = 'N/A';
            $nextOfficeEmployeeName = 'N/A';
            
            // Check if there's a next area in the current timeline
            if ($aopApplication->currentTimeline && $aopApplication->currentTimeline->next_area_id) {
                $nextArea = AssignedArea::with('user')->find($aopApplication->currentTimeline->next_area_id);
                
                if ($nextArea) {
                    $nextOfficeAreaName = $nextArea->full_area_name ?? 'N/A';
                    $nextOfficeAreaCode = $nextArea->area_code ?? 'N/A';
                    $nextOfficeEmployeeName = $nextArea->user ? $nextArea->user->name : 'N/A';
                }
            }
            
            // Load the user and assigned area for the AOP application
            $aopApplication->load(['user', 'user.assignedArea']);
            
            // Determine the email context
            $context = 'update_user';
            if ($recipient->id === $aopApplication->user_id) {
                $context = 'request';
            } elseif ($nextArea && $recipient->id === $nextArea->user_id) {
                $context = 'update_next_user';
            }
            
            // Use test email in non-production environments or use a specific email for testing
            $recipientEmail = Config::get('app.env') === 'production' 
                ? $recipient->email 
                : Config::get('mail.test_email', 'test@example.com');
            
            // Prepare email data to match the template structure
            $emailData = [
                'aop_application' => $aopApplication,
                'subject' => $title,
                'message' => $message,
                'action_user' => $actionUser,
                
                // Template specific fields
                'status' => $aopApplication->status ?? 'Pending',
                'remarks' => $message,
                'context' => $context,
                'reference_number' => $aopApplication->reference_number ?? ('AOP-' . $aopApplication->id),
                
                // Requester details
                'requester_name' => $aopApplication->user->name ?? 'N/A',
                'requester_area' => $aopApplication->user->assignedArea->full_area_name ?? 'N/A',
                'requester_area_code' => $aopApplication->user->assignedArea->area_code ?? 'N/A',
                'requested_at' => $aopApplication->created_at ? 
                    $aopApplication->created_at->format('F d, Y h:i A') : 
                    now()->format('F d, Y h:i A'),
                
                // Current office details
                'current_office_area' => $actionUser && isset($actionUser->assignedArea) ? 
                    $actionUser->assignedArea->full_area_name : 'N/A',
                'current_office_area_code' => $actionUser && isset($actionUser->assignedArea) ? 
                    $actionUser->assignedArea->area_code : 'N/A',
                'current_office_employee_name' => $actionUser ? $actionUser->name : 'N/A',
                
                // Next office details
                'next_office_area' => $nextOfficeAreaName,
                'next_office_area_code' => $nextOfficeAreaCode,
                'next_office_employee_name' => $nextOfficeEmployeeName,
                
                // Additional info
                'updated_by' => $actionUser ? $actionUser->name : 'System',
                'transaction_type' => 'Annual Operating Plan',
                'transaction_code' => $aopApplication->reference_number ?? ('AOP-' . $aopApplication->id)
            ];
            
            // Send the email
            return $this->sendEmail($recipientEmail, $emailData);
            
        } catch (\Exception $e) {
            Log::error('Error preparing approval email: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'recipient_id' => $recipientUserId,
                'aop_id' => $aopApplication->id
            ]);
            
            return false;
        }
    }
}
