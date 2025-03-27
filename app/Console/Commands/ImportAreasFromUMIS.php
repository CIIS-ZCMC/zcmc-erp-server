<?php

namespace App\Console\Commands;

use App\Models\AssignedArea;
use App\Models\User;
use App\Services\UMISService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAreasFromUMIS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:areas-from-umis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import assigned areas data from UMIS';

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
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting import of areas from UMIS...');
        
        $response = $this->umisService->getAreas();
        
        if (!$response) {
            $this->error('Failed to fetch areas from UMIS.');
            return Command::FAILURE;
        }

        // Extract data array from the response
        $areasData = $response['data'] ?? null;
        
        if (!$areasData || !is_array($areasData)) {
            $this->error('Invalid data format received from UMIS.');
            return Command::FAILURE;
        }

        $this->info('Received ' . count($areasData) . ' areas from UMIS.');
        
        try {
            DB::beginTransaction();
            
            // Clear existing assigned areas (if needed based on sync strategy)
            // AssignedArea::truncate(); // Uncomment if complete resync is needed
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($areasData as $areaData) {
                try {
                    // // Get the UMIS user ID from the data
                    // $umisUserId = $areaData['employee_profile_id'] ?? null;
                    
                    // if (!$umisUserId) {
                    //     $this->warn("Area data missing employee_profile_id. Skipping.");
                    //     continue;
                    // }
                    
                    // // Find user by UMIS ID
                    // $user = User::where('umis_id', $umisUserId)->first();
                    
                    // if (!$user) {
                    //     $this->warn("User with UMIS ID {$umisUserId} not found. Skipping area assignment.");
                    //     continue;
                    // }
                    
                    // Update or create the assignment
                    $assignedArea = AssignedArea::updateOrCreate(
                        [
                            'user_id' => $areaData['employee_profile_id'] ?? null,
                            'division_id' => $areaData['division_id'] ?? null,
                            'department_id' => $areaData['department_id'] ?? null,
                            'section_id' => $areaData['section_id'] ?? null,
                            'unit_id' => $areaData['unit_id'] ?? null,
                        ]
                    );

                    if (!$assignedArea) {
                        $this->warn("Failed to assign area for user: {$areaData['employee_profile_id']}");
                        continue;
                    }
                    
                    $this->info("Assigned area for user: {$areaData['employee_profile_id']}");
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing area: " . $e->getMessage());
                    Log::error('UMIS Area Import - Error processing area', [
                        'area_data' => $areaData,
                        'error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $this->info("Import completed. Processed areas: $successCount success, $errorCount errors.");
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
