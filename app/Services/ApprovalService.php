<?php

namespace App\Services;

use App\Models\AssignedArea;
use App\Models\ApplicationTimeline;
use App\Models\AopApplication;
use App\Models\Division;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AssignedAreaResource;
use App\Helpers\TransactionLogHelper;

class ApprovalService
{

    protected NotificationService $notificationService;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Create a timeline entry for the application with appropriate next area routing based on workflow
     *
     * @param object $aop_application
     * @param string $status
     * @param string|null $remarks
     * @return ApplicationTimeline|null
     */
    public function createApplicationTimeline(object $aop_application, $current_user, $aop_user, string $status, string $remarks = null)
    {
        try {
            // Determine the next area ID based on the workflow
            $next_area_id = null;
            $division_chief_area = null;
            $omcc_area = null;
            $planning_unit_area = null;

            $current_user_assigned_area = $current_user->assignedArea;
            $aop_user_assigned_area = $aop_user->assignedArea;

            // Check the existing timelines to determine the current approval stage
            $latest_timeline = ApplicationTimeline::where('aop_application_id', $aop_application->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Initialize the stage based on existing timelines or start a new workflow
            $stage = 'init';

            if ($latest_timeline) {
                // If there's an existing timeline, determine the current stage
                if ($status === 'approved') {
                    // Determine the next stage based on the current area

                    // Check if current area is Planning Unit (section id = 53)
                    if ($current_user_assigned_area->section_id == 53) {
                        $stage = 'division_chief';

                        // Get the Division Chief for the current area
                        $division_chief = null;

                        // Use explicit relationship method calls
                        $unit = $aop_user_assigned_area->unit()->first();
                        $section = $aop_user_assigned_area->section()->first();
                        $department = $aop_user_assigned_area->department()->first();

                        if ($unit) {
                            $division_chief = $unit->getDivisionChief();
                        } elseif ($section) {
                            $division_chief = $section->getDivisionChief();
                        } elseif ($department) {
                            $division_chief = $department->getDivisionChief();
                        }

                        // Get the assigned area for the division chief
                        if ($division_chief) {
                            $division_chief_area = AssignedArea::where('user_id', $division_chief->id)->first();
                        }

                        if ($division_chief_area) {
                            $next_area_id = $division_chief_area->id;
                        }
                    } else {
                        // Get the division for this area
                        $division = $current_user_assigned_area->division()->first();

                        // Check if current area is Division Chief (but not OMCC)
                        if ($division && $current_user_assigned_area->user_id == $division->head_id && $division->id != 1) {
                            $stage = 'omcc';

                            // Next is Medical Center Chief (Office of the Medical Center Chief, division id = 1)
                            $omcc_division = Division::find(1); // OMCC Division (id = 1)

                            if ($omcc_division && $omcc_division->head_id) {
                                // Get the assigned area for the Medical Center Chief
                                $omcc_area = AssignedArea::where('user_id', $omcc_division->head_id)->first();
                            }

                            if ($omcc_area) {
                                $next_area_id = $omcc_area->id;
                            }
                        } elseif ($division && $division->id == 1 && $current_user_assigned_area->user_id == $division->head_id) {
                            // This is the final approval stage
                            $stage = 'final';
                            $next_area_id = null; // No next area as this is the final stage
                        }
                    }
                } elseif ($status === 'returned') {
                    // If returned, send back to the original requestor
                    $next_area_id = $aop_application->user_id;
                }
            } else {
                // If this is the first timeline entry (no existing timeline)
                // Initial submission - route to Planning Unit (section id = 53)
                $stage = 'planning_unit';

                // Get the Planning section (id = 53)
                $planningSection = Section::find(53);

                if ($planningSection) {
                    Log::info('Found Planning section', ['section_id' => $planningSection->id, 'name' => $planningSection->name]);

                    // Get the head of the Planning section if available
                    if ($planningSection->head_id) {
                        $planning_unit_area = AssignedArea::where('section_id', 53)
                            ->where('user_id', $planningSection->head_id)
                            ->first();

                        Log::info('Looking for Planning head area', [
                            'head_id' => $planningSection->head_id,
                            'found' => $planning_unit_area ? 'yes' : 'no'
                        ]);
                    }

                    // If no specific head found, get any assigned area in this section
                    if (!$planning_unit_area) {
                        $planning_unit_area = AssignedArea::where('section_id', 53)->first();
                        Log::info('Using any Planning section area', ['found' => $planning_unit_area ? 'yes' : 'no']);
                    }
                } else {
                    Log::error('Planning section not found', ['section_id' => 53]);
                }

                if ($planning_unit_area) {
                    $next_area_id = $planning_unit_area->id;
                }
            }

            // Create the timeline entry
            // First check if we have a next area ID - if not, we need to handle it
            if ($next_area_id === null && $status === 'approved' && $stage !== 'final') {
                Log::warning('No next area ID determined for non-final stage', [
                    'stage' => $stage,
                    'status' => $status,
                    'application_id' => $aop_application->id
                ]);

                // Set a fallback if we're in the initial stage and can't find the Planning Unit
                if ($stage === 'init' || $stage === 'planning_unit') {
                    // Fallback to any Planning section area as last resort
                    $planningFallback = AssignedArea::where('section_id', 53)->first();
                    if ($planningFallback) {
                        $next_area_id = $planningFallback->id;
                        Log::info('Using fallback Planning area', ['area_id' => $next_area_id]);
                    }
                }
            }

            $timeline = new ApplicationTimeline([
                'aop_application_id' => $aop_application->id,
                'user_id' => $aop_user->id,
                'approver_user_id' => $current_user->id,
                'current_area_id' => $current_user_assigned_area->id,
                'next_area_id' => $next_area_id,
                'status' => $status,
                'remarks' => $remarks,
                'action_date' => now(),
            ]);

            $timeline->save();

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
