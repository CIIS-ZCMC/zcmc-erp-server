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
use App\Services\EmailService;

class ApprovalService
{

    protected NotificationService $notificationService;
    protected EmailService $emailService;

    /**
     * Create a new class instance.
     */
    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
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
    public function createApplicationTimeline(object $aop_application, object $current_user, object $aop_user, string $status, string $remarks = null)
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

                        if ($unit) {
                            $division_chief = $unit->getDivisionChief();
                        } elseif ($section) {
                            $division_chief = $section->getDivisionChief();
                        } elseif ($department) {
                            $division_chief = $department->getDivisionChief();
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
                        // Get the division for this area
                        $division = $aop_user_assigned_area->division()->first();

                        if (!$division) {
                            Log::warning('No division found for current user assigned area', [
                                'assigned_area_id' => $aop_user_assigned_area->id
                            ]);
                        }

                        // Check if current area is Division Chief (but not OMCC)
                        if ($division && $aop_user->id == $division->head_id && $division->id != 1) {
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

            // Create the timeline entry
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

            // Prepare common transaction data for notifications and emails
            $transactionData = [
                'transaction_type' => 'AOP Application',
                'transaction_code' => $aop_application->application_code ?? "AOP-{$aop_application->id}",
                'status' => $status,
                'remarks' => $remarks ?? 'No remarks provided',
                'requested_at' => $aop_application->created_at->format('Y-m-d H:i:s'),
                'requester_employee_id' => $aop_user->employee_id ?? 'N/A',
                'requester_name' => $aop_user->name,
                'requester_area' => $aop_user_assigned_area->name ?? 'N/A',
                'requester_area_code' => $aop_user_assigned_area->area_code ?? 'N/A',
                'current_office_area' => $current_user_assigned_area->name ?? 'N/A',
                'current_office_area_code' => $current_user_assigned_area->area_code ?? 'N/A',
                'current_office_employee_name' => $current_user->name,
                'updated_by' => $current_user->name,
                'aop_application_id' => $aop_application->id
            ];

            // Send notifications to the next area user if there is a next area
            if ($next_area_id) {
                $nextArea = AssignedArea::find($next_area_id);
                if ($nextArea && $nextArea->user_id) {
                    $nextUser = User::find($nextArea->user_id);
                    if ($nextUser) {
                        // In-app notification
                        $this->notificationService->notify($nextUser, [
                            'title' => 'AOP Application Requires Your Action',
                            'description' => "An AOP application has been routed to you for review.",
                            'module_path' => "/aop-application/{$aop_application->id}",
                            'aop_application_id' => $aop_application->id,
                            'status' => $status
                        ]);

                        // Email notification if user has email
                        if ($nextUser->email) {
                            $nextUserEmailData = array_merge($transactionData, [
                                'subject' => 'AOP Application Requires Your Action',
                                'next_office_employee_name' => $nextUser->name,
                                'next_office_area_code' => $nextArea->area_code ?? 'N/A',
                                'next_office_area' => $nextArea->name ?? 'N/A',
                            ]);

                            $this->emailService->sendTransactionUpdate(
                                $nextUser->email,
                                'update_next_user',
                                $nextUserEmailData
                            );
                        }
                    }
                }
            }

            // Notify the AOP application owner about the status change
            // In-app notification
            $this->notificationService->notify($aop_user, [
                'title' => 'AOP Application Status Update',
                'description' => "Your AOP application has been {$status}." .
                                ($remarks ? " Remarks: {$remarks}" : ""),
                'module_path' => "/aop-application/{$aop_application->id}",
                'aop_application_id' => $aop_application->id,
                'status' => $status
            ]);

            // Email notification if user has email
            if ($aop_user->email) {
                $ownerEmailData = array_merge($transactionData, [
                    'subject' => "AOP Application Status: {$status}",
                    'next_office_area_code' => $nextArea->area_code ?? 'N/A',
                    'next_office_area' => $nextArea->name ?? 'N/A',
                ]);

                $this->emailService->sendTransactionUpdate(
                    $aop_user->email,
                    'update_user',
                    $ownerEmailData
                );
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
}
