<?php

namespace App\Console\Commands;

use App\Models\AssignedArea;
use App\Models\Unit;
use App\Models\User;
use App\Services\UMISService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Division;
use App\Models\Department;
use App\Models\Section;


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
    public function handle(): int
    {
        $this->info('Starting import of areas from UMIS...');

        $response = $this->umisService->getOrganizationStructure();

        if (!$response) {
            $this->error('Failed to fetch areas from UMIS.');
            return Command::FAILURE;
        }

        // Extract data array from the response
        $areasData = $response['data'] ?? null;
        $divisions = $areasData['divisions'] ?? null;
        $departments = $areasData['departments'] ?? null;
        $sections = $areasData['sections'] ?? null;
        $units = $areasData['units'] ?? null;

        if (!$areasData || !is_array($areasData)) {
            $this->error('Invalid data format received from UMIS.');
            return Command::FAILURE;
        }

        if (!$divisions || !is_array($divisions)) {
            $this->error('Invalid divisions data format received from UMIS.');
            return Command::FAILURE;
        }

        if (!$departments || !is_array($departments)) {
            $this->error('Invalid departments data format received from UMIS.');
            return Command::FAILURE;
        }

        if (!$sections || !is_array($sections)) {
            $this->error('Invalid sections data format received from UMIS.');
            return Command::FAILURE;
        }

        if (!$units || !is_array($units)) {
            $this->error('Invalid units data format received from UMIS.');
            return Command::FAILURE;
        }

        $this->info('Received ' . count($areasData) . ' areas from UMIS.');

        try {
            DB::beginTransaction();

            // Clear existing assigned areas (if needed based on sync strategy)
            // AssignedArea::truncate(); // Uncomment if complete resync is needed

            $successCount = 0;
            $errorCount = 0;

            // Populate divisions from UMIS
            foreach($divisions as $division) {
                try {
                    $user = $division['chief_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $division['chief_employee_profile_id'])->first():null;
                    $oic_user = $division['oic_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $division['oic_employee_profile_id'])->first():null;

                    Division::updateOrCreate(
                        ['id' => $division['id']],
                        [
                            'id' => $division['id'],
                            'area_id' => $division['area_id'],
                            'name' => $division['name'],
                            'code' => $division['code'],
                            'head_id' => $user!== null? $user->id: null,
                            'oic_id' => $oic_user !== null? $oic_user->id: null
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing division {$division['id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            // Populate departments from UMIS
            foreach($departments as $department) {
                try {
                    $user = $department['head_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $department['head_employee_profile_id'])->first():null;
                    $oic_user = $department['oic_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $department['oic_employee_profile_id'])->first():null;
                    $division = $department['division_id'] !== null? Division::where('id', $department['division_id'])->first():null;

                    Department::updateOrCreate(
                        ['id' => $department['id']],
                        [
                            'id' => $department['id'],
                            'area_id' => $department['area_id'],
                            'name' => $department['name'],
                            'code' => $department['code'],
                            'division_id' => $division !== null? $division->id: null,
                            'head_id' => $user!== null? $user->id: null,
                            'oic_id' => $oic_user !== null? $oic_user->id: null
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing department {$department['id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            foreach($sections as $section) {
                try {
                    $user = $section['supervisor_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $section['supervisor_employee_profile_id'])->first():null;
                    $oic_user = $section['oic_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $section['oic_employee_profile_id'])->first():null;
                    $division = $section['division_id'] !== null? Division::where('id', $section['division_id'])->first():null;
                    $department = $section['department_id'] !== null? Department::where('id', $section['department_id'])->first():null;

                    Section::updateOrCreate(
                        ['id' => $section['id']],
                        [
                            'id' => $section['id'],
                            'area_id' => $section['area_id'],
                            'name' => $section['name'],
                            'code' => $section['code'],
                            'division_id' => $division !== null? $division->id: null,
                            'department_id' => $department !== null? $department->id: null,
                            'head_id' => $user!== null? $user->id: null,
                            'oic_id' => $oic_user !== null? $oic_user->id: null
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing section {$section['id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            foreach($units as $unit) {
                try {
                    $user = $unit['head_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $unit['head_employee_profile_id'])->first():null;
                    $oic_user = $unit['oic_employee_profile_id'] !== null ? User::where("umis_employee_profile_id", $unit['oic_employee_profile_id'])->first():null;
                    $section = $unit['section_id'] !== null? Section::where('id', $unit['section_id'])->first():null;

                    Unit::updateOrCreate(
                        ['id' => $unit['id']],
                        [
                            'id' => $unit['id'],
                            'area_id' => $unit['area_id'],
                            'name' => $unit['name'],
                            'code' => $unit['code'],
                            'section_id' => $section !== null? $section->id : null,
                            'head_id' => $user!== null? $user->id: null,
                            'oic_id' => $oic_user !== null? $oic_user->id: null
                        ]
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Error processing unit {$unit['id']}: " . $e->getMessage());
                    $errorCount++;
                }
            }

            //         // Find user by UMIS ID
            //         $user = User::where('umis_id', $umisUserId)->first();

            //         if (!$user) {
            //             $this->warn("User with UMIS ID {$umisUserId} not found. Skipping area assignment.");
            //             continue;
            //         }

            //         // Update or create the assignment
            //         $assignedArea = AssignedArea::updateOrCreate(
            //             [
            //                 'user_id' => $areaData['employee_profile_id'] ?? null,
            //                 'division_id' => $areaData['division_id'] ?? null,
            //                 'department_id' => $areaData['department_id'] ?? null,
            //                 'section_id' => $areaData['section_id'] ?? null,
            //                 'unit_id' => $areaData['unit_id'] ?? null,
            //             ]
            //         );

            //         if (!$assignedArea) {
            //             $this->warn("Failed to assign area for user: {$areaData['employee_profile_id']}");
            //             continue;
            //         }

            //         $this->info("Assigned area for user: {$areaData['employee_profile_id']}");

            //         $successCount++;
            //     } catch (\Exception $e) {
            //         $this->error("Error processing area: " . $e->getMessage());
            //         Log::error('UMIS Area Import - Error processing area', [
            //             'area_data' => $areaData,
            //             'error' => $e->getMessage()
            //         ]);
            //         $errorCount++;
            //     }
            // }

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
