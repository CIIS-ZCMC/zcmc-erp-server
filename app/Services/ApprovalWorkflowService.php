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

class ApprovalWorkflowService
{
    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new class instance.
     *
     * @param NotificationService|null $notificationService
     */
    public function __construct(?NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?: new NotificationService();
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
            if (!$aopApplication instanceof AopApplication) {
                Log::error("Cannot create timeline - AOP application not found or invalid", [
                    'application_id' => $application_id
                ]);
                return null;
            }

            if (!$aopApplication) {
                Log::error("Cannot create timeline - AOP application not found", [
                    'application_id' => $application_id
                ]);
                return null;
            }

            // Get current assigned area details
            $currentArea = AssignedArea::with(['department', 'section', 'unit', 'division'])
                ->where('id', $current_area_id)
                ->where('user_id', $userId)
                ->first();

            if (!$currentArea) {
                Log::error("Cannot create timeline - Current area not found", [
                    'current_area_id' => $current_area_id
                ]);
                return null;
            }

            // Determine the next area ID based on the workflow
            $next_area_id = null;
            $divisionChiefArea = null;
            $omccArea = null;
            $planningUnitArea = null;

            // Check the existing timelines to determine the current approval stage
            $latestTimeline = ApplicationTimeline::where('aop_application_id', $application_id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Initialize the stage based on existing timelines or start a new workflow
            $stage = 'init';

            // Log the beginning of processing with detailed information
            Log::info('Processing AOP timeline', [
                'application_id' => $application_id,
                'user_id' => $userId,
                'current_area_id' => $current_area_id,
                'status' => $status,
                'latestTimeline' => $latestTimeline ? $latestTimeline->id : 'none'
            ]);

            if ($latestTimeline) {
                // If there's an existing timeline, determine the current stage
                if ($status === 'approved') {
                    // Determine the next stage based on the current area

                    // Check if current area is Planning Unit (section id = 48)
                    if ($currentArea->section_id == 48) {
                        $stage = 'division_chief';

                        // Get the Division Chief for the current area
                        $divisionChief = null;

                        // Use explicit relationship method calls
                        $unit = $currentArea->unit()->first();
                        $section = $currentArea->section()->first();
                        $department = $currentArea->department()->first();

                        if ($unit) {
                            $divisionChief = $unit->getDivisionChief();
                        } elseif ($section) {
                            $divisionChief = $section->getDivisionChief();
                        } elseif ($department) {
                            $divisionChief = $department->getDivisionChief();
                        }

                        Log::info('Division Chief determined', [
                            'division_chief_id' => $divisionChief ? $divisionChief->id : null,
                            'division_chief_name' => $divisionChief ? $divisionChief->name : 'Not found'
                        ]);

                        // Get the assigned area for the division chief
                        if ($divisionChief) {
                            $divisionChiefArea = AssignedArea::where('user_id', $divisionChief->id)->first();
                        }

                        if ($divisionChiefArea) {
                            $next_area_id = $divisionChiefArea->id;
                        }
                    } else {
                        // Get the division for this area
                        $division = $currentArea->division()->first();

                        // Check if current area is Division Chief (but not OMCC)
                        if ($division && $currentArea->user_id == $division->head_id && $division->id != 1) {
                            $stage = 'omcc';

                            // Next is Medical Center Chief (Office of the Medical Center Chief, division id = 1)
                            $omccDivision = Division::find(1); // OMCC Division (id = 1)

                            if ($omccDivision && $omccDivision->head_id) {
                                // Get the assigned area for the Medical Center Chief
                                $omccArea = AssignedArea::where('user_id', $omccDivision->head_id)->first();
                            }

                            if ($omccArea) {
                                $next_area_id = $omccArea->id;
                            }
                        } elseif ($division && $division->id == 1 && $currentArea->user_id == $division->head_id) {
                            // This is the final approval stage
                            $stage = 'final';
                            $next_area_id = null; // No next area as this is the final stage
                        }
                    }
                } elseif ($status === 'returned') {
                    // If returned, send back to the original requestor
                    $next_area_id = $aopApplication->created_by_area_id;
                }
            } else {
                // If this is the first timeline entry (no existing timeline)
                // Initial submission - route to Planning Unit (section id = 48)
                $stage = 'planning_unit';

                // Get the Planning section (id = 48)
                $planningSection = Section::find(48);

                if ($planningSection) {
                    Log::info('Found Planning section', ['section_id' => $planningSection->id, 'name' => $planningSection->name]);

                    // Get the head of the Planning section if available
                    if ($planningSection->head_id) {
                        $planningUnitArea = AssignedArea::where('section_id', 48)
                            ->where('user_id', $planningSection->head_id)
                            ->first();

                        Log::info('Looking for Planning head area', [
                            'head_id' => $planningSection->head_id,
                            'found' => $planningUnitArea ? 'yes' : 'no'
                        ]);
                    }

                    // If no specific head found, get any assigned area in this section
                    if (!$planningUnitArea) {
                        $planningUnitArea = AssignedArea::where('section_id', 48)->first();
                        Log::info('Using any Planning section area', ['found' => $planningUnitArea ? 'yes' : 'no']);
                    }
                } else {
                    Log::error('Planning section not found', ['section_id' => 48]);
                }

                if ($planningUnitArea) {
                    $next_area_id = $planningUnitArea->id;
                }
            }

            // Create the timeline entry
            // First check if we have a next area ID - if not, we need to handle it
            if ($next_area_id === null && $status === 'approved' && $stage !== 'final') {
                Log::warning('No next area ID determined for non-final stage', [
                    'stage' => $stage,
                    'status' => $status,
                    'application_id' => $application_id
                ]);

                // Set a fallback if we're in the initial stage and can't find the Planning Unit
                if ($stage === 'init' || $stage === 'planning_unit') {
                    // Fallback to any Planning section area as last resort
                    $planningFallback = AssignedArea::where('section_id', 48)->first();
                    if ($planningFallback) {
                        $next_area_id = $planningFallback->id;
                        Log::info('Using fallback Planning area', ['area_id' => $next_area_id]);
                    }
                }
            }

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

            // Log the transaction after timeline creation
            $logCode = 'AOP_TIMELINE_' . strtoupper($status);
            TransactionLogHelper::register($timeline, $logCode);

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
