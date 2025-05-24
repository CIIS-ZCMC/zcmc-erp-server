<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AopApplication;
use App\Models\ApplicationTimeline;
use App\Models\PpmpApplication;
use App\Models\User;
use App\Models\AssignedArea;
use Carbon\Carbon;

class ApplicationTimelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates application timeline entries for AOP applications
     * Each AOP application will have only one timeline entry
     */
    public function run(): void
    {
        // Get existing AOP applications created by AopApplicationSeeder
        $aopApplications = AopApplication::all();

        if ($aopApplications->isEmpty()) {
            $this->command->info('No AOP applications found. Please run AopApplicationSeeder first.');
            return;
        }

        // Get users for approvals
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please seed users first.');
            return;
        }

        // Get assigned areas for routing
        $assignedAreas = AssignedArea::all();
        if ($assignedAreas->isEmpty()) {
            $this->command->info('No assigned areas found. Please seed assigned areas first.');
            return;
        }

        // Check for PPMP applications or create a default one if none exists
        $ppmpApplication = PpmpApplication::first();
        if (!$ppmpApplication) {
            // Create a default PPMP application if needed
            $ppmpId = 1; // Will be used as a placeholder
        } else {
            $ppmpId = $ppmpApplication->id;
        }

        foreach ($aopApplications as $application) {
            // If we don't have at least one user and one assigned area, we can't create a timeline
            if ($users->isEmpty() || $assignedAreas->isEmpty()) {
                $this->command->info('No users or assigned areas available for timeline creation.');
                return;
            }

            // Create a single timeline entry for each AOP application
            $this->createApplicationTimeline(
                $application->id,
                $ppmpId,
                $users->first()->id,
                $assignedAreas->first()->id,
                'pending',
                'Application submitted for initial review',
                Carbon::now()->subDays(rand(5, 30)), // Random date within the last 30 days
                null,
                null
            );
        }

        $this->command->info('Application timeline entries created successfully.');
    }

    /**
     * Helper method to create application timeline entries
     */
    private function createApplicationTimeline(
        $aopApplicationId,
        $ppmpApplicationId,
        $userId,
        $currentAreaId,
        $status,
        $remarks,
        $dateCreated,
        $dateApproved,
        $dateReturned
    ): void {
        try {
            ApplicationTimeline::create([
                'aop_application_id' => $aopApplicationId,
                'ppmp_application_id' => $ppmpApplicationId,
                'user_id' => $userId,
                'current_area_id' => $currentAreaId,
                'status' => $status,
                'remarks' => $remarks,
                'created_at' => $dateCreated,
                'date_approved' => $dateApproved,
                'date_returned' => $dateReturned,
            ]);
            $this->command->line("Created timeline entry for AOP application: {$aopApplicationId}");
        } catch (\Exception $e) {
            $this->command->error("Error creating timeline entry: {$e->getMessage()}");
        }
    }
}
