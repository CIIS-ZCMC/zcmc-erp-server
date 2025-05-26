<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class EmailService
{
    /**
     * Send a notification email to a single recipient
     *
     * @param string $recipient The email address of the recipient
     * @param array $data Data to be passed to the email template
     * @return void
     */
    public function sendNotification(string $recipient, array $data): void
    {
        try {
            // Ensure required fields are present
            $this->validateEmailData($data);

            // Dispatch the email job to the queue
            SendEmailJob::dispatch($recipient, $data)->onQueue('email');

            Log::info('Email notification queued', [
                'recipient' => $recipient,
                'subject' => $data['subject']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue email notification', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send the same notification email to multiple recipients
     *
     * @param array $recipients Array of email addresses
     * @param array $data Data to be passed to the email template
     * @return void
     */
    public function sendBulkNotification(array $recipients, array $data): void
    {
        try {
            // Ensure required fields are present
            $this->validateEmailData($data);

            foreach ($recipients as $recipient) {
                // Dispatch an email job for each recipient
                SendEmailJob::dispatch($recipient, $data)->onQueue('email');
            }

            Log::info('Bulk email notifications queued', [
                'recipient_count' => count($recipients),
                'subject' => $data['subject']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue bulk email notifications', [
                'recipients' => $recipients,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send a transaction status update email
     *
     * @param string $recipient The email address of the recipient
     * @param string $context The context of the email (request, update_user, update_next_user)
     * @param array $transactionData Transaction data for the email
     * @return void
     */
    public function sendTransactionUpdate(string $recipient, string $context, array $transactionData): void
    {
        try {
            // Prepare email data with context
            $emailData = array_merge($transactionData, ['context' => $context]);

            // Ensure required fields are present
            $this->validateEmailData($emailData);

            // Add default subject if not provided
            if (!isset($emailData['subject'])) {
                $status = $emailData['status'] ?? 'Updated';
                $transactionType = $emailData['transaction_type'] ?? 'Transaction';
                $emailData['subject'] = "$transactionType Status: $status";
            }

            // Dispatch the email job to the queue
            SendEmailJob::dispatch($recipient, $emailData)->onQueue('email');

            Log::info('Transaction update email queued', [
                'recipient' => $recipient,
                'subject' => $emailData['subject'],
                'context' => $context
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue transaction update email', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send an Annual Operation Plan (AOP) status update email
     *
     * @param string $recipient The email address of the recipient
     * @param string $context The context of the email (update_user, update_next_approver)
     * @param array $aopData AOP application data for the email
     * @return void
     */
    public function sendAopStatusUpdate(string $recipient, string $context, array $aopData): void
    {
        try {
            // Prepare email data with context
            $emailData = array_merge($aopData, ['context' => $context]);

            // Ensure required fields are present
            $this->validateEmailData($emailData);

            // Add default subject if not provided
            if (!isset($emailData['subject'])) {
                $status = $emailData['status'] ?? 'Updated';
                $emailData['subject'] = "ZCMC Annual Operation Plan: $status";
            }

            // Make sure we have a status message
            if (!isset($emailData['status_message'])) {
                $status = $emailData['status'] ?? 'updated';
                $emailData['status_message'] = "Your Annual Operation Plan has been $status.";
            }

            // Add AOP-specific template variables
            $emailData['email_title'] = 'Annual Operation Plan Status Update';
            $emailData['email_heading'] = 'ZCMC Annual Operation Plan System';
            $emailData['email_preheader'] = 'Important update regarding your Annual Operation Plan';

            // Add application ID if present for tracking purposes
            if (isset($aopData['aop_application_id'])) {
                $emailData['aop_application_id'] = $aopData['aop_application_id'];
            }

            // Dispatch the email job to the queue
            SendEmailJob::dispatch($recipient, $emailData)->onQueue('email');

            Log::info('AOP status update email queued', [
                'recipient' => $recipient,
                'subject' => $emailData['subject'],
                'context' => $context,
                'aop_application_id' => $aopData['aop_application_id'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue AOP status update email', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if emails have been sent to a specific recipient
     *
     * @param string $recipient Email address to check
     * @param string|null $subject Optional subject line to filter by
     * @param string|null $status Optional status to filter by (Sent, Failed)
     * @return Collection Collection of email logs
     */
    public function checkRecipientEmails(string $recipient, ?string $subject = null, ?string $status = null): Collection
    {
        $query = EmailLog::where('recipient_email', $recipient);

        if ($subject) {
            $query->where('subject', 'like', "%$subject%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Check if emails have been sent related to a specific AOP application
     *
     * @param int $aopApplicationId The AOP application ID
     * @param string|null $status Optional status to filter by (Sent, Failed)
     * @return Collection Collection of email logs
     */
    public function checkApplicationEmails(int $aopApplicationId, ?string $status = null): Collection
    {
        $query = EmailLog::where('body', 'like', "%\"aop_application_id\":$aopApplicationId%");

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get summary of email delivery status
     *
     * @param int|null $days Number of days to look back, null for all time
     * @return array Summary statistics of email delivery
     */
    public function getEmailDeliveryStats(?int $days = 7): array
    {
        $query = EmailLog::query();

        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $total = $query->count();
        $sent = $query->where('status', 'Sent')->count();
        $failed = $query->where('status', 'Failed')->count();

        return [
            'period' => $days ? "Last $days days" : 'All time',
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Ensure email data contains required fields
     *
     * @param array $data Email data to validate
     * @throws \InvalidArgumentException
     */
    private function validateEmailData(array $data): void
    {
        // Check for subject
        if (empty($data['subject'])) {
            throw new \InvalidArgumentException('Email subject is required');
        }
    }
}
