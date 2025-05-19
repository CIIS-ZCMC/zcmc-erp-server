<?php

namespace App\Jobs;

use App\Helpers\RealtimeCommunicationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class EmitNewDataToSocketConnectionJob implements ShouldQueue
{
    use Queueable;
    protected string $targetSocketEndpointBaseOnTableRecord;
    protected $newRegisteredData;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(string $endPoint, $newRegisteredData)
    {
        $this->targetSocketEndpointBaseOnTableRecord = $endPoint;
        $this->newRegisteredData = $newRegisteredData;
        $this->queue = 'socket';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = RealtimeCommunicationHelper::emitNewTransactionRecord(
                $this->targetSocketEndpointBaseOnTableRecord,
                $this->newRegisteredData
            );

            if ($response->successful()) {
                Log::info('Emit successful', [
                    'endpoint' => $this->targetSocketEndpointBaseOnTableRecord,
                    'data' => $this->newRegisteredData,
                    'response' => $response->body(),
                ]);
            } else {
                Log::error('Emit failed', [
                    'endpoint' => $this->targetSocketEndpointBaseOnTableRecord,
                    'data' => $this->newRegisteredData,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception if the request fails
            Log::critical('Emit request threw an exception', [
                'endpoint' => $this->targetSocketEndpointBaseOnTableRecord,
                'data' => $this->newRegisteredData,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
