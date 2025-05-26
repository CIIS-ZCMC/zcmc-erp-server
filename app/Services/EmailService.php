<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Log;

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
                'context' => $context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Validate that the required fields are present in the email data
     *
     * @param array $data The email data to validate
     * @throws \InvalidArgumentException If required fields are missing
     * @return void
     */
    private function validateEmailData(array $data): void
    {
        $requiredFields = ['subject'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Required field '$field' is missing in email data");
            }
        }

        // Additional context-specific validation
        if (isset($data['context'])) {
            switch ($data['context']) {
                case 'request':
                case 'update_user':
                    if (!isset($data['requester_name'])) {
                        throw new \InvalidArgumentException("Requester name is required for context '{$data['context']}'");
                    }
                    break;

                case 'update_next_user':
                    if (!isset($data['next_office_employee_name'])) {
                        throw new \InvalidArgumentException("Next office employee name is required for context 'update_next_user'");
                    }
                    break;
            }
        }
    }
}
