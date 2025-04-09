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
            // Make sure we have at least 2 users and 2 assigned areas
            if ($users->count() < 2 || $assignedAreas->count() < 2) {
                $this->command->info('Not enough users or assigned areas for complete timeline creation.');
                // Still create at least one timeline entry with what we have
                $this->createApplicationTimeline(
                    $application->id,
                    $ppmpId,
                    $users->first()->id,
                    $assignedAreas->first()->id,
                    $assignedAreas->count() > 1 ? $assignedAreas[1]->id : $assignedAreas->first()->id,
                    'pending',
                    'Application submitted for initial review',
                    Carbon::now()->subDays(30),
                    null,
                    null
                );
                continue;
            }
            
            // First timeline entry - Initial submission
            $this->createApplicationTimeline(
                $application->id,
                $ppmpId,
                $users[0]->id,
                $assignedAreas[0]->id,
                $assignedAreas->count() > 1 ? $assignedAreas[1]->id : $assignedAreas[0]->id,
                'pending',
                'Application submitted for initial review',
                Carbon::now()->subDays(30),
                null,
                null
            );
            
            // Second timeline entry - If we have at least 2 assigned areas
            if ($assignedAreas->count() > 1 && $users->count() > 1) {
                $this->createApplicationTimeline(
                    $application->id,
                    $ppmpId,
                    $users[1]->id,
                    $assignedAreas[1]->id,
                    $assignedAreas->count() > 2 ? $assignedAreas[2]->id : $assignedAreas[0]->id,
                    'approved',
                    'Approved by first reviewer. Forwarded to next in line.',
                    Carbon::now()->subDays(25),
                    Carbon::now()->subDays(23),
                    null
                );
            }
            
            // Third timeline entry - If we have at least 3 assigned areas
            if ($assignedAreas->count() > 2 && $users->count() > 2) {
                $this->createApplicationTimeline(
                    $application->id,
                    $ppmpId,
                    $users[2]->id,
                    $assignedAreas[2]->id,
                    $assignedAreas->count() > 3 ? $assignedAreas[3]->id : $assignedAreas[0]->id,
                    'approved',
                    'Approved by second reviewer. Forwarded to final reviewer.',
                    Carbon::now()->subDays(20),
                    Carbon::now()->subDays(18),
                    null
                );
            }
            
            // Final timeline entry - Always create this to complete the flow
            $this->createApplicationTimeline(
                $application->id,
                $ppmpId,
                $users[0]->id,
                $assignedAreas->count() > 2 ? $assignedAreas[2]->id : $assignedAreas[0]->id,
                null,
                'approved',
                'Final approval granted. AOP application complete.',
                Carbon::now()->subDays(15),
                Carbon::now()->subDays(10),
                null
            );
        }
    }
    
    /**
     * Helper method to create application timeline entries
     */
    private function createApplicationTimeline(
        $aopApplicationId,
        $ppmpApplicationId,
        $userId,
        $currentAreaId,
        $nextAreaId,
        $status,
        $remarks,
        $dateCreated,
        $dateApproved,
        $dateReturned
    ): void {
        try {
            ApplicationTimeline::create([
                'aop_application_id' => $aopApplicationId,
                'ppmp_application_id' => $ppmpApplicationId, // Already set with fallback in the run method
                'user_id' => $userId,
                'current_area_id' => $currentAreaId,
                'next_area_id' => $nextAreaId ?? $currentAreaId, // Default to current if null
                'status' => $status,
                'remarks' => $remarks,
                'date_created' => $dateCreated,
                'date_approved' => $dateApproved,
                'date_returned' => $dateReturned,
            ]);
            $this->command->line("Created timeline entry for AOP application: {$aopApplicationId}");
        } catch (\Exception $e) {
            $this->command->error("Error creating timeline entry: {$e->getMessage()}");
        }
    }
}
