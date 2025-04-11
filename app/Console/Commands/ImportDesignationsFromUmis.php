<?php

namespace App\Console\Commands;

use App\Services\UMISService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Designation;

class ImportDesignationsFromUmis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:designations-from-umis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import designations data from UMIS';

    /**
     * The UMIS service instance.
     *
     * @var \App\Services\UMISService
     */
    protected $umisService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\UMISService $umisService
     * @return void
     */
    public function __construct(UMISService $umisService)
    {
        parent::__construct();
        $this->umisService = $umisService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import of designations from UMIS...');

        $response = $this->umisService->getDesignations();

        if (!$response) {
            $this->error('Failed to import designations from UMIS');
            return 1;
        }

        // Extract data array from the response
        $designationsData = $response['data'] ?? null;

        if (!$designationsData || !is_array($designationsData)) {
            $this->error('Invalid data format received from UMIS.');
            return Command::FAILURE;
        }

        $this->info('Received ' . count($designationsData) . ' designations from UMIS.');

        try {
            DB::beginTransaction();

            // Clear existing designations (if needed based on sync strategy)
            $successCount = 0;
            $errorCount = 0;

            // Populate divisions from UMIS
            foreach ($designationsData as $designation) {
                try {
                    Designation::updateOrCreate(
                        ['id' => $designation['id']],
                        [
                            'umis_designation_id' => $designation['id'],
                            'name' => $designation['name'],
                            'code' => $designation['code'],
                            'probation' => $designation['probation'],
                        ]
                    );
                    $successCount++;
                } catch (\Exception $exception) {
                    $this->error("Error processing designation {$designation['id']}: " . $exception->getMessage());
                    $errorCount++;
                }
            }

            DB::commit();

            $this->info("Import completed. Processed designations: $successCount success, $errorCount errors.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            Log::error('UMIS Area Import - Fatal error', [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
