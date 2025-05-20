<?php

namespace App\Jobs;

use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $recipient;
    protected array $email_data;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(string $recipient, array $email_data)
    {
        $this->recipient = $recipient;
        $this->email_data = $email_data;
        $this->queue = 'email'; // Set the queue name here instead
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Send the email
            // Mail::to($this->recipient)->send(new ApprovalNotification($this->email_data));

            // // Log the successful email action
            // EmailLog::create([
            //     'recipient_email' => $this->recipient,
            //     'subject' => $this->email_data['subject'] ?? 'No Subject',
            //     'body' => json_encode($this->email_data), // Optional: store the entire data
            //     'status' => 'Sent',
            //     'sent_at' => now(),
            // ]);
        } catch (\Exception $e) {
            // Log the failed email action
            // EmailLog::create([
            //     'recipient_email' => $this->recipient,
            //     'subject' => $this->email_data['subject'] ?? 'No Subject',
            //     'body' => json_encode($this->email_data), // Optional: store the entire data
            //     'status' => 'Failed',
            //     'error_message' => $e->getMessage(),
            // ]);
            Log::error($e->getMessage());
        }
    }
}
