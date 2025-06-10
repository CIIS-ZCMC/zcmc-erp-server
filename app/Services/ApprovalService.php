<?php

namespace App\Services;

use App\Models\AssignedArea;
use App\Models\ApplicationTimeline;
use App\Models\AopApplication;
use App\Models\Division;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AssignedAreaResource;
use App\Helpers\TransactionLogHelper;
use App\Services\NotificationService;

class ApprovalService
{

    protected NotificationService $notificationService;
    /**
     * Create a new class instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a timeline entry for the application with appropriate next area routing based on workflow
     *
     * @param object $aop_application
     * @param object $current_user
     * @param object $aop_user
     * @param string $status
     * @param string|null $remarks
     * @return ApplicationTimeline|array
     * @throws \Exception
     */
    public function createApplicationTimeline(object $aop_application, object $current_user, object $aop_user, string $status, string $remarks = null): ApplicationTimeline|array
    {
        try {
            // Validate essential inputs
            if (!$aop_application || !isset($aop_application->id)) {
                throw new \InvalidArgumentException('Invalid AOP application object provided');
            }

            if (!$current_user || !isset($current_user->id) || !$current_user->assignedArea) {
                throw new \InvalidArgumentException('Invalid current user or missing assigned area');
            }

            if (!$aop_user || !isset($aop_user->id) || !$aop_user->assignedArea) {
                throw new \InvalidArgumentException('Invalid AOP user or missing assigned area');
            }

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
                        $division = $aop_user_assigned_area->division()->first();

                        Log::info('Relationship method calls', [
                            'unit' => $unit,
                            'section' => $section,
                            'department' => $department,
                            'division' => $division
                        ]);

                        if ($unit) {
                            $division_chief = $unit->getDivisionChief();
                        } elseif ($section) {
                            $division_chief = $section->getDivisionChief();
                        } elseif ($department) {
                            $division_chief = $department->getDivisionChief();
                        } elseif ($division) {
                            $division_chief = $division->getDivisionChief();
                        }

                        Log::info('Division chief for the current area', [
                            'division_chief_id' => $division_chief

                        ]);

                        // Get the assigned area for the division chief
                        if ($division_chief) {
                            $division_chief_area = AssignedArea::where('user_id', $division_chief->id)->first();
                            if ($division_chief_area) {
                                $next_area_id = $division_chief_area->id;

                            } else {
                                Log::warning('No assigned area found for division chief', [
                                    'division_chief_id' => $division_chief->id
                                ]);
                                // Fallback: Use the AOP user's assigned area as the next area
                                $next_area_id = $aop_user_assigned_area->id;
                                Log::info('Fallback: Routing to AOP user area', [
                                    'aop_user_id' => $aop_user->id,
                                    'area_id' => $aop_user_assigned_area->id
                                ]);
                            }
                        } else {
                            Log::warning('No division chief found for area', [
                                'unit_id' => $unit ? $unit->id : null,
                                'section_id' => $section ? $section->id : null,
                                'department_id' => $department ? $department->id : null,
                                'division_id' => $division ? $division->id : null
                            ]);

                            // If no division chief is found, use the AOP user's assigned area as the next area
                            $next_area_id = $aop_user_assigned_area->id;
                            Log::info('Fallback: No division chief found, routing to AOP user area', [
                                'aop_user_id' => $aop_user->id,
                                'area_id' => $aop_user_assigned_area->id
                            ]);
                        }
                    } else {
                        // Get the section this unit belongs to
                        // Get the division for the current assigned area
                        $division = null;
                        if ($aop_user_assigned_area->unit_id) {
                            $division = $aop_user_assigned_area->unit->section->division;
                        } else if ($aop_user_assigned_area->section_id) {
                            $division = $aop_user_assigned_area->section->division;
                        } else if ($aop_user_assigned_area->department_id) {
                            $division = $aop_user_assigned_area->department->division;
                        } else {
                            $division = $aop_user_assigned_area->division;
                        }
                        Log::warning('No division found for current user assigned area', [
                            'saection_id' => $aop_user_assigned_area->section_id,
                            'assigned_area_id' => $aop_user_assigned_area->id,
                            'division_id' => $division ? $division->id : null
                        ]);

                        // Check if current area is Division Chief (but not OMCC)
                        if ($division && $division->id != 1) {
                            $stage = 'omcc';

                            // Next is Medical Center Chief (Office of the Medical Center Chief, division id = 1)
                            $omcc_division = Division::find(1); // OMCC Division (id = 1)

                            if ($omcc_division && $omcc_division->head_id) {
                                // Get the assigned area for the Medical Center Chief
                                $omcc_area = AssignedArea::where('user_id', $omcc_division->head_id)->first();
                            } else {
                                Log::warning('OMCC division or head not found', [
                                    'omcc_division_exists' => (bool)$omcc_division,
                                    'has_head_id' => $omcc_division ? (bool)$omcc_division->head_id : false
                                ]);
                            }

                            if ($omcc_area) {
                                $next_area_id = $omcc_area->id;
                            } else {
                                Log::warning('No assigned area found for OMCC head', [
                                    'omcc_head_id' => $omcc_division ? $omcc_division->head_id : null
                                ]);
                            }
                        } elseif ($division && $division->id == 1 && $aop_user_assigned_area->user_id == $division->head_id) {
                            Log::info('Routing to final approval stage (Medical Center Chief)', [
                                'medical_center_chief_id' => $division->head_id
                            ]);
                            // This is the final approval stage
                            $stage = 'final';
                            $next_area_id = null; // No next area as this is the final stage
                        }
                    }
                } elseif ($status === 'returned') {

                    $applicationTimeline = $aop_application->applicationTimelines()->where('aop_application_id', $aop_application->id)->first();
                    // If returned, send back to the original requestor
                    $next_area_id = $applicationTimeline->current_area_id;
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
                    throw new \RuntimeException('Planning section (ID: 53) not found in the system');
                }

                if ($planning_unit_area) {
                    $next_area_id = $planning_unit_area->id;
                } else {
                    Log::error('No planning unit area found', ['section_id' => 53]);
                    throw new \RuntimeException('No planning unit area found for routing');
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
                // Show error if we can't find the next area
                if ($stage === 'init' || $stage === 'planning_unit') {
                    Log::error('No next area ID determined for initial or planning unit stage', [
                        'stage' => $stage,
                        'status' => $status,
                        'application_id' => $aop_application->id
                    ]);
                    throw new \RuntimeException('No next area ID determined for initial or planning unit stage');
                }
            }

            // Update the AOP application status
            $aop_application->status = $status;
            $aop_application->remarks = $remarks ?? null;
            $aop_application->save();

            // Create the timeline entry
            $timeline = new ApplicationTimeline([
                'aop_application_id' => $aop_application->id,
                'user_id' => $aop_user->id,
                'approver_user_id' => $current_user->id,
                'current_area_id' => $current_user_assigned_area->id,
                'next_area_id' => $next_area_id,
                'status' => $status,
                'remarks' => $remarks ?? null,
                'date_approved' => now(),
            ]);

            $timeline->save();

            // Send notifications based on status
            if ($status === 'returned') {
                // Get current area name for the notification
                $current_area_name = $this->getAreaNameFromAssignedArea($current_user_assigned_area);

                // Notify the application owner about the returned application
                $this->notificationService->notify($aop_user, [
                    'title' => 'AOP Application Returned',
                    'description' => "Your AOP application has been returned from {$current_area_name} by {$current_user->name}." . ($remarks ? " Remarks: $remarks" : ""),
                    'module_path' => "/aop",
                    'aop_application_id' => $aop_application->id,
                    'status' => $status,
                    'remarks' => $remarks,
                    'user_id' => $aop_user->id,
                    'current_area' => $current_area_name
                ]);

                // If there's a next area (to be returned to), notify that area's user too
                $nextArea = $next_area_id ? AssignedArea::find($next_area_id) : null;
                $nextUser = $nextArea ? User::find($nextArea->user_id) : null;
                if ($nextUser) {
                    $next_area_name = $this->getAreaNameFromAssignedArea($nextArea);
                    $this->notificationService->notify($nextUser, [
                        'title' => 'AOP Application Requires Your Action',
                        'description' => "An AOP application has been returned to you for revision from {$current_area_name} by {$current_user->name}." . ($remarks ? " Remarks: $remarks" : ""),
                        'module_path' => "/aop-approval/objectives/{$aop_application->id}",
                        'aop_application_id' => $aop_application->id,
                        'status' => $status,
                        'remarks' => $remarks,
                        'current_area' => $current_area_name,
                        'next_area' => $next_area_name
                    ]);
                }
            } else {
                // Check if this is the final approval by OMCC Chief
                $is_final_approval = ($stage === 'final' && $status === 'approved' &&
                    $current_user_assigned_area->division_id == 1 &&
                    $current_user_assigned_area->user_id == Division::find(1)->head_id);

                if ($is_final_approval) {
                    // Special notification for final approval
                    $this->notificationService->notify($aop_user, [
                        'title' => 'AOP Application FULLY APPROVED',
                        'description' => "Congratulations! Your AOP application has received final approval from the Medical Center Chief, {$current_user->name}. Your application process is now complete.",
                        'module_path' => "/aop",
                        'aop_application_id' => $aop_application->id,
                        'status' => 'approved',
                        'current_area' => $this->getAreaNameFromAssignedArea($current_user_assigned_area),
                        'is_final' => true
                    ]);

                    // For final approval, we still create the timeline entry but don't need other notifications
                    return $timeline;
                }

                // Send notifications to the next area user if there is a next area
                $nextArea = AssignedArea::find($next_area_id);
                $nextUser = $nextArea ? User::find($nextArea->user_id) : null;
                if ($nextUser) {
                    $current_area_name = $this->getAreaNameFromAssignedArea($current_user_assigned_area);
                    $next_area_name = $this->getAreaNameFromAssignedArea($nextArea);

                    // Determine the stage name for better status clarity
                    $stage_description = '';
                    if ($stage === 'planning_unit') {
                        $stage_description = 'Planning Unit';
                    } elseif ($stage === 'division_chief') {
                        $stage_description = 'Division Chief';
                    } elseif ($stage === 'omcc') {
                        $stage_description = 'Office of the Medical Center Chief';
                    } elseif ($stage === 'final') {
                        $stage_description = 'Final Approval';
                    }

                    $this->notificationService->notify($nextUser, [
                        'title' => 'AOP Application Requires Your Action',
                        'description' => "An AOP application has been routed to you for review. It was approved by {$current_user->name} from {$current_area_name} and now requires your action.",
                        'module_path' => "/aop-approval/objectives/{$aop_application->id}",
                        'aop_application_id' => $aop_application->id,
                        'status' => $status,
                        'current_area' => $current_area_name,
                        'next_area' => $next_area_name,
                        'stage' => $stage_description
                    ]);
                }
            }

            // Only send general status update notification if not the final approval (as we've already sent a specialized one)
            if (!($stage === 'final' && $status === 'approved')) {
                // Notify the AOP application owner about the status change
                $current_area_name = $this->getAreaNameFromAssignedArea($current_user_assigned_area);
                $next_stage_message = '';

                // Add information about next stage
                if ($nextArea && $nextUser) {
                    $next_area_name = $this->getAreaNameFromAssignedArea($nextArea);
                    $next_stage_message = " It has been forwarded to {$next_area_name} for the next approval step.";
                } elseif ($stage === 'final' && $status === 'approved') {
                    $next_stage_message = " This is the final approval stage. Your AOP application process is now complete.";
                }

                $this->notificationService->notify($aop_user, [
                    'title' => 'AOP Application Status Update',
                    'description' => "Your AOP application has been {$status} by {$current_user->name} from {$current_area_name}." .
                                    $next_stage_message .
                                    ($remarks ? " Remarks: {$remarks}" : ""),
                    'module_path' => "/aop",
                    'aop_application_id' => $aop_application->id,
                    'status' => $status,
                    'current_area' => $current_area_name,
                    'next_area' => $next_area_id ? $this->getAreaNameFromAssignedArea($nextArea) : null,
                    'remarks' => $remarks
                ]);
            }
            // Log successful creation
            Log::info('Application timeline created successfully', [
                'timeline_id' => $timeline->id,
                'application_id' => $aop_application->id,
                'stage' => $stage,
                'status' => $status
            ]);

            return $timeline;
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors
            Log::error('Validation error in createApplicationTimeline: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null
            ]);

            // Re-throw with more context for API response
            throw new \InvalidArgumentException('Failed to create application timeline: ' . $e->getMessage(), 0, $e);
        } catch (\RuntimeException $e) {
            // Handle runtime errors
            Log::error('Runtime error in createApplicationTimeline: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null
            ]);

            // Re-throw with more context for API response
            throw new \RuntimeException('Failed to process application timeline: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Handle all other errors
            $errorDetails = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null,
                'current_user_id' => $current_user->id ?? null,
                'aop_user_id' => $aop_user->id ?? null,
                'status' => $status
            ];

            Log::error('Error creating application timeline: ', $errorDetails);

            // Return detailed error information instead of null
            return [
                'success' => false,
                'error' => 'An error occurred while creating the application timeline',
                'details' => $errorDetails,
            ];
        }
    }

    /**
     * Create an initial timeline entry for a newly created AOP application
     *
     * @param object $aop_application The newly created AOP application
     * @param object $current_user The user creating the application
     * @param string|null $remarks Optional remarks for the initial submission
     * @return ApplicationTimeline|array
     * @throws \Exception
     */
    public function createInitialApplicationTimeline(object $aop_application, object $current_user, string $remarks = null): ApplicationTimeline|array
    {
        try {
            // Validate essential inputs
            if (!$aop_application || !isset($aop_application->id)) {
                throw new \InvalidArgumentException('Invalid AOP application object provided');
            }

            if (!$current_user || !isset($current_user->id) || !$current_user->assignedArea) {
                throw new \InvalidArgumentException('Invalid current user or missing assigned area');
            }

            // For new applications, the current user is also the AOP user
            $aop_user = $current_user;
            $current_user_assigned_area = $current_user->assignedArea;

            // Initial submission - route to Planning Unit (section id = 53)
            $next_area_id = null;
            $planning_unit_area = null;

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
                throw new \RuntimeException('Planning section (ID: 53) not found in the system');
            }

            if ($planning_unit_area) {
                $next_area_id = $planning_unit_area->id;
            } else {
                Log::error('No planning unit area found', ['section_id' => 53]);
                throw new \RuntimeException('No planning unit area found for routing');
            }

            // Create the timeline entry
            $timeline = new ApplicationTimeline([
                'aop_application_id' => $aop_application->id,
                'user_id' => $aop_user->id,
                'approver_user_id' => $current_user->id,
                'current_area_id' => $current_user_assigned_area->id,
                'next_area_id' => $next_area_id,
                'status' => 'pending', // Initial status must be 'pending' based on the database schema
                'remarks' => $remarks,
            ]);

            $timeline->save();

            // Send notification to the Planning Unit user
            if ($next_area_id) {
                $nextArea = AssignedArea::find($next_area_id);
                if ($nextArea && $nextArea->user_id) {
                    $nextUser = User::find($nextArea->user_id);
                    if ($nextUser) {
                        $this->notificationService->notify($nextUser, [
                            'title' => 'New AOP Application Submitted',
                            'description' => "A new AOP application has been submitted by {$current_user->name} from {$this->getAreaNameFromAssignedArea($current_user_assigned_area)} and requires your review.",
                            'module_path' => "/aop-approval/objectives/{$aop_application->id}",
                            'aop_application_id' => $aop_application->id,
                            'status' => 'pending'
                        ]);
                    }
                }
            }

            // Log successful creation
            Log::info('Initial application timeline created successfully', [
                'timeline_id' => $timeline->id,
                'application_id' => $aop_application->id,
                'status' => 'pending'
            ]);

            return $timeline;
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors
            Log::error('Validation error in createInitialApplicationTimeline: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null
            ]);

            // Re-throw with more context for API response
            throw new \InvalidArgumentException('Failed to create initial application timeline: ' . $e->getMessage(), 0, $e);
        } catch (\RuntimeException $e) {
            // Handle runtime errors
            Log::error('Runtime error in createInitialApplicationTimeline: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null
            ]);

            // Re-throw with more context for API response
            throw new \RuntimeException('Failed to process initial application timeline: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            // Handle all other errors
            $errorDetails = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'application_id' => $aop_application->id ?? null,
                'current_user_id' => $current_user->id ?? null
            ];

            Log::error('Error creating initial application timeline: ', $errorDetails);

            // Return detailed error information instead of null
            return [
                'success' => false,
                'error' => 'An error occurred while creating the initial application timeline',
                'details' => $errorDetails,
            ];
        }
    }

    /**
     * Helper method to get a descriptive name for an area from an AssignedArea object
     *
     * @param AssignedArea $assignedArea The assigned area to get the name for
     * @return string The descriptive name of the area
     */
    private function getAreaNameFromAssignedArea(AssignedArea $assignedArea): string
    {
        // Try to get the most specific organizational structure first
        if ($assignedArea->unit_id) {
            $unit = $assignedArea->unit()->first();
            if ($unit && $unit->name) {
                return $unit->area_id ?? $unit->name;
            }
        }

        if ($assignedArea->section_id) {
            $section = $assignedArea->section()->first();
            if ($section && $section->name) {
                return $section->area_id ?? $section->name;
            }
        }

        if ($assignedArea->department_id) {
            $department = $assignedArea->department()->first();
            if ($department && $department->name) {
                return $department->area_id ?? $department->name; ;
            }
        }

        if ($assignedArea->division_id) {
            $division = $assignedArea->division()->first();
            if ($division && $division->name) {
                return $division->area_id ?? $division->name;
            }
        }

        // If no specific area name is found, return the ID as a fallback
        return "Area ID: {$assignedArea->id}";
    }
}
