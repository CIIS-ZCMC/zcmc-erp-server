<?php

namespace App\Services;

use App\Models\AssignedArea;
use App\Models\ApplicationTimeline;
use App\Models\AopApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AssignedAreaResource;

class ApprovalWorkflowService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a timeline entry for the application with appropriate next area routing based on workflow
     * 
     * @param int $application_id
     * @param int $userId
     * @param int $current_area_id
     * @param string $status
     * @param string $remarks
     * @return ApplicationTimeline|null
     */
    public function createApplicationTimeline($application_id, $userId, $current_area_id, $status, $remarks = null)
    {
        try {
            // Get the AOP application for reference
            $aopApplication = AopApplication::find($application_id);

            // ALGORITHM
            // 1. When an AOP request is submitted, it is initially stored in the application timeline
            //    with the following details:
            //    - Status: "Pending"
            //    - Current Area ID
            //    - Next Area ID
            // 2. This function is triggered when the process reaches the recorded Next Area ID.
            // 3. Once triggered, it updates the status of the AOP accordingly.
            //  id to which area will be approving next.
            // 4. OMCC: Plannit Unit details (first phase of the timeline for approving aop request)
            //      - in section table, id = 48
            // 5. Division Chief -> are the head of Divisions areas
            //      - in division table,depends who is the division chief of the unit approver
            // 6. Medical Center Chief -> it's a division but specifically for Office of the Medical Center Chief 
            //      - in division table, specifically id = 1, or the Office of the Medical Center Chief
            
            if (!$aopApplication) {
                Log::error("Cannot create timeline - AOP application not found", [
                    'application_id' => $application_id
                ]);
                return null;
            }

            // Get current assigned area details
            $currentArea = AssignedArea::with(['department', 'section', 'unit', 'division'])
                ->find($current_area_id);

            if (!$currentArea) {
                Log::error("Cannot create timeline - Current area not found", [
                    'current_area_id' => $current_area_id
                ]);
                return null;
            }

            // Determine the next area ID based on the workflow
            $next_area_id = null;

            // Check the existing timelines to determine the current approval stage
            $latestTimeline = ApplicationTimeline::where('aop_application_id', $application_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Initialize the stage based on existing timelines or start a new workflow
            $stage = 'init';
            
            if ($latestTimeline) {
                // If there's an existing timeline, determine the current stage
                if ($status === 'approved') {
                    // Determine the next stage based on the current area
                    
                    // Check if current area is Planning Unit (section id = 48)
                    if ($currentArea->section && $currentArea->section->id == 48) {
                        $stage = 'division_chief';
                        
                        // Look for the Division Chief's area of the unit that's approving
                        $divisionChiefArea = AssignedArea::whereHas('division', function($query) use ($currentArea) {
                            // Get the division associated with the current unit/section
                            if ($currentArea->unit) {
                                $query->where('id', $currentArea->unit->division_id);
                            } elseif ($currentArea->section) {
                                $query->where('id', $currentArea->section->division_id);
                            } elseif ($currentArea->department) {
                                $query->where('id', $currentArea->department->division_id);
                            }
                        })
                        ->where('is_head', true) // Assuming division chiefs are marked as head
                        ->first();
                        
                        if ($divisionChiefArea) {
                            $next_area_id = $divisionChiefArea->id;
                        }
                    } 
                    // Check if current area is Division Chief 
                    elseif ($currentArea->division && $currentArea->is_head && $currentArea->division->id != 1) {
                        $stage = 'omcc';
                        
                        // Next is Medical Center Chief (Office of the Medical Center Chief, division id = 1)
                        $omccArea = AssignedArea::whereHas('division', function($query) {
                            $query->where('id', 1); // OMCC division id
                        })
                        ->where('is_head', true) // The head of OMCC
                        ->first();
                        
                        if ($omccArea) {
                            $next_area_id = $omccArea->id;
                        }
                    } 
                    // Check if current area is Medical Center Chief (OMCC)
                    elseif ($currentArea->division && $currentArea->division->id == 1 && $currentArea->is_head) {
                        // This is the final approval stage
                        $stage = 'final';
                        $next_area_id = null; // No next area as this is the final stage
                    }
                } elseif ($status === 'returned') {
                    // If returned, send back to the original requestor
                    $next_area_id = $aopApplication->created_by_area_id;
                }
            } else {
                // If this is the first timeline entry (no existing timeline)
                // Start with Planning Unit as the first approval stage
                $planningUnitArea = AssignedArea::whereHas('section', function($query) {
                    $query->where('id', 48); // Planning Unit section id
                })
                // ->where('is_head', true) // Likely want the head of the Planning Unit
                ->first();
                
                if ($planningUnitArea) {
                    $next_area_id = $planningUnitArea->id;
                }
            }

            // Create the timeline entry
            $timeline = new ApplicationTimeline([
                'aop_application_id' => $application_id,
                'user_id' => $userId,
                'current_area_id' => $current_area_id,
                'next_area_id' => $next_area_id,
                'status' => $status,
                'remarks' => $remarks,
                'action_date' => now(),
            ]);

            $timeline->save();
            
            // Log the timeline creation for debugging
            Log::info('Application timeline created', [
                'timeline_id' => $timeline->id,
                'application_id' => $application_id,
                'stage' => $stage,
                'current_area_id' => $current_area_id,
                'next_area_id' => $next_area_id,
                'status' => $status
            ]);

            return $timeline;
           
        } catch (\Exception $e) {
            Log::error('Error creating application timeline: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

}
