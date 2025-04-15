<?php

namespace App\Console\Commands;

use App\Models\AssignedArea;
use App\Services\UMISService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAssignedAreasFromUMIS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:assigned-areas-from-umis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import assigned areas from UMIS';

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
        $this->info('Starting import of assigned areas from UMIS...');

        $response = $this->umisService->getAssignedAreas();

        if (!$response) {
            $this->error('Failed to fetch assigned areas from UMIS.');
            return Command::FAILURE;
        }

        // Extract data array from the response
        $assignedAreasData = $response['data'] ?? null;
        
        if (!$assignedAreasData || !is_array($assignedAreasData)) {
            $this->error('Invalid data format received from UMIS.');
            return Command::FAILURE;
        }

        $this->info('Received ' . count($assignedAreasData) . ' assigned areas from UMIS.');

        try {
            // Start a transaction
            DB::beginTransaction();

            // Clear existing assigned areas
            AssignedArea::query()->delete();

            $successCount = 0;
            $errorCount = 0;

            // Populate assigned areas from UMIS
            foreach ($assignedAreasData as $assignedArea) {
                try {
                    AssignedArea::create([
                        'user_id' => $assignedArea['employee_profile_id'],
                        'division_id' => $assignedArea['division_id'] ?? null,
                        'department_id' => $assignedArea['department_id'] ?? null,
                        'section_id' => $assignedArea['section_id'] ?? null,
                        'unit_id' => $assignedArea['unit_id'] ?? null,
                    ]);
                    $successCount++;
                } catch (\Exception $exception) {
                    $this->error("Error processing assigned area for user {$assignedArea['user_id']}: " . $exception->getMessage());
                    Log::error("Error processing assigned area for user {$assignedArea['user_id']}: " . $exception->getMessage());
                    $errorCount++;
                }
            }

            // Commit the transaction
            DB::commit();

            $this->info("Successfully imported $successCount assigned areas.");
            if ($errorCount > 0) {
                $this->warn("Failed to import $errorCount assigned areas.");
            }

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            // Rollback the transaction on error
            DB::rollBack();
            
            $this->error('An error occurred during import: ' . $exception->getMessage());
            Log::error('Assigned areas import failed: ' . $exception->getMessage());
            
            return Command::FAILURE;
        }
    }
}
