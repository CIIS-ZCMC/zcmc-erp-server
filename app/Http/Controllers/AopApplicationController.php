<?php

namespace App\Http\Controllers;

use App\Helpers\TransactionLogHelper;
use App\Http\Requests\AopApplicationRequest;
use App\Http\Requests\ProcessAopRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\AopRemarksResource;
use App\Http\Resources\AopRequestResource;
use App\Http\Resources\AopResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Http\Resources\DesignationResource;
use App\Http\Resources\AssignedAreaResource;
use App\Models\AopApplication;
use App\Models\AssignedArea;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\PpmpItem;
use App\Models\PurchaseType;
use App\Models\Section;
use App\Models\SuccessIndicator;
use App\Models\Unit;
use App\Models\User;
use App\Models\ApplicationTimeline;
use App\Models\Log;
use App\Services\ApprovalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Services\AopVisibilityService;

class AopApplicationController extends Controller
{

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Get metadata for API responses
     *
     * @param string $method The method requesting metadata
     * @return array The metadata for the response
     */
    protected function getMetadata(string $method): array
    {
        $metadata = [
            'timestamp' => now(),
            'method' => $method
        ];

        if ($method === 'getAopApplications') {
            $metadata['statuses'] = ['pending', 'approved', 'returned'];
            $metadata['current_year'] = date('Y');
        }

        return $metadata;
    }

    public function index()
    {

        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.objective',
                'applicationObjectives.otherObjective',
                'applicationObjectives.successIndicator',
                'applicationObjectives.otherSuccessIndicator',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.resources.item',
                'applicationObjectives.activities.responsiblePeople.user',
                'applicationObjectives.activities.comments',
            ])
            ->get();

        return AopApplicationResource::collection($aopApplications);
    }

    public function getUserAopSummary(Request $request)
    {

        $user_id = $request->user()->id;
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();
        if (!$assignedArea) {
            return response()->json(['message' => 'User has no assigned area.'], 404);
        }

        $area = $assignedArea->findDetails();

        if (!$area || !isset($area['sector'], $area['details']['id'])) {
            return response()->json(['message' => 'Assigned area details are incomplete.'], 422);
        }


        $userAopApplication = AopApplication::where('sector', $area['sector'])
            ->where('sector_id', $area['details']['id'])
            ->orderByDesc('year')
            ->first();

        if (!$userAopApplication) {
            return response()->json(['data' => []]);
        }

        return $this->getAopApplicationSummary($userAopApplication->id);
    }
    public function getAopApplicationSummary($aopApplicationId)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.successIndicator',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople',
            'applicationObjectives.activities.comments',
        ])->findOrFail($aopApplicationId);

        $objectives = $aopApplication->applicationObjectives;
        $activities = $objectives->flatMap->activities;

        $totalObjectives = $objectives->count();
        $totalActivities = $activities->count();
        $totalResources = $activities->flatMap->resources->count();
        $totalResponsiblePeople = $activities->flatMap->responsiblePeople;

        $totalComments = $activities->flatMap->comments->count();
        $totalSuccessIndicators = $objectives->pluck('successIndicator')->filter()->unique('id')->count();

        $gadRelatedActivities = $activities->filter(fn($act) => $act->is_gad_related);
        $notGadRelatedActivities = $activities->filter(fn($act) => !$act->is_gad_related);

        $totalGadRelated = $gadRelatedActivities->count();
        $totalNotGadRelated = $notGadRelatedActivities->count();

        $totalActivityCost = $activities->sum('cost');

        // Count distinct area combinations
        $totalAreas = $totalResponsiblePeople->map(fn($p) => [
            'unit_id' => $p->unit_id,
            'department_id' => $p->department_id,
            'division_id' => $p->division_id,
            'section_id' => $p->section_id,
        ])->unique()->count();

        // Count unique designation IDs
        $totalJobPositions = $totalResponsiblePeople->pluck('designation_id')->filter()->unique()->count();

        // Count unique user IDs
        $totalUsers = $totalResponsiblePeople->pluck('user_id')->filter()->unique()->count();

        return response()->json([
            'aop_application_id' => $aopApplication->id,
            'mission' => $aopApplication->mission,
            'year' => $aopApplication->year,
            'total_objectives' => $totalObjectives,
            'total_success_indicators' => $totalSuccessIndicators,
            'total_activities' => $totalActivities,
            'total_gad_related' => $totalGadRelated,
            'total_not_gad_related' => $totalNotGadRelated,
            'total_cost' => $totalActivityCost,
            'total_resources' => $totalResources,
            'total_comments' => $totalComments,
            'total_responsible_people' => $totalResponsiblePeople->count(),
            'total_areas' => $totalAreas,
            'total_job_positions' => $totalJobPositions,
            'total_users' => $totalUsers,
        ]);
    }

    public function showUserTimeline(Request $request)
    {
        $user_id = $request->user()->id;

        // Get the user's assigned area
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();

        if (!$assignedArea) {
            return response()->json(['message' => 'User has no assigned area.'], 404);
        }

        $area = $assignedArea->findDetails();

        if (!$area || !isset($area['sector'], $area['details']['id'])) {
            return response()->json(['message' => 'Assigned area details are incomplete.'], 422);
        }

        // Find the AOP application for the user's sector, sector_id, and current year
        $aopApplication = AopApplication::with('applicationTimelines')
            ->where('sector', $area['sector'])
            ->where('sector_id', $area['details']['id'])
            ->orderByDesc('year')
            ->first();

        if (!$aopApplication) {
            return response()->json(['message' => 'No AOP application found for this year in your assigned area.'], 404);
        }

        return ApplicationTimelineResource::collection(
            $aopApplication->applicationTimelines
        );
    }
    // public function showTimeline($aopApplicationId)
    // {
    //     $aopApplication = AopApplication::with('applicationTimelines')
    //         ->findOrFail($aopApplicationId);

    //     return ApplicationTimelineResource::collection(
    //         $aopApplication->applicationTimelines
    //     );
    // }

    public function update(AopApplicationRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $aopApplication = AopApplication::with([
                'applicationObjectives.activities.resources.item',
                'ppmpApplication',
            ])->findOrFail($id);

            $user_id = $request->user()->id;
            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();
            $planningOfficer = Section::where('name', 'Planning Unit')->first();
            $planningOfficerId = optional($planningOfficer)->head_id;
            $curr_user = User::find($user_id);
            $curr_user_authorization_pin = $curr_user->authorization_pin;

            if ($curr_user_authorization_pin !== $request->authorization_pin) {
                return response()->json([
                    'message' => 'Invalid Authorization Pin'
                ], Response::HTTP_BAD_REQUEST);
            }

            $aopApplication->update($request->only([
                'mission',
                'status',
                'has_discussed',
                'remarks',
            ]));

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    $activity->target()->delete();
                    $activity->resources()->delete();
                    $activity->responsiblePeople()->delete();
                }
                $objective->activities()->delete();
                $objective->otherObjective()->delete();
                $objective->otherSuccessIndicator()->delete();
            }

            $aopApplication->applicationObjectives()->delete();

            foreach ($request->application_objectives as $objectiveData) {
                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'objective_id' => $objectiveData['objective_id'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                    'objective_code' => $objectiveData['objective_code'] ?? null,
                ]);

                if (($applicationObjective->objective->description ?? null) === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->othersObjective()->create([
                        'description' => $objectiveData['others_objective'],
                    ]);
                }

                if (($applicationObjective->successIndicator->description ?? null) === 'Others' && isset($objectiveData['other_success_indicator'])) {
                    $applicationObjective->otherSuccessIndicator()->create([
                        'description' => $objectiveData['other_success_indicator'],
                    ]);
                }

                foreach ($objectiveData['activities'] as $activityData) {
                    $activity = $applicationObjective->activities()->create([
                        'activity_code' => $this->generateUniqueActivityCode(),
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);

                    $activity->target()->create($activityData['target']);

                    foreach ($activityData['resources'] as $resourceData) {
                        $activity->resources()->create($resourceData);
                    }

                    foreach ($activityData['responsible_people'] as $personData) {
                        $activity->responsiblePeople()->create($personData);
                    }
                }
            }

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'ppmp_application_id' => null,
                'action' => "Update Aop",
                'action_by' => $user_id,
            ]);

            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');
            $ppmpTotal = 0;

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            $aopApplication->ppmpApplication()->update([
                'ppmp_total' => $ppmpTotal,
                'remarks' => $request->remarks ?? null,
            ]);

            $ppmpApplication = $aopApplication->ppmpApplication;
            $ppmpApplication->ppmpItems()->delete();

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $estimatedBudget = $resource->item->estimated_budget;
                            $totalAmount = $resource->quantity * $estimatedBudget;

                            $ppmpApplication->ppmpItems()->create([
                                'item_id' => $resource->item_id,
                                'total_quantity' => $resource->quantity,
                                'estimated_budget' => $estimatedBudget,
                                'total_amount' => $totalAmount,
                                'expense_class' => $resource->expense_class,
                                'remarks' => null,
                            ]);
                        }
                    }
                }
            }

            Log::create([
                'aop_application_id' => null,
                'ppmp_application_id' => $ppmpApplication->id,
                'action' => "Update Ppmp",
                'action_by' => $user_id,
            ]);

            // Approval flow
            $aop_user = User::find($aopApplication->user_id);
            $approval_service = new ApprovalService($this->notificationService);
            $timeline = $approval_service->createInitialApplicationTimeline(
                $aopApplication,
                $curr_user,
                $request->remarks
            );

            if (!$timeline) {
                DB::rollBack();
                return response()->json([
                    'message' => 'AOP application timeline not created',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();
            return response()->json(['message' => 'AOP Application updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update AOP Application.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    protected function generateUniqueActivityCode(): string
    {
        do {
            $code = 'ACT-' . strtoupper(Str::random(6)); // e.g. ACT-X8Y9Z1
        } while (\App\Models\Activity::where('activity_code', $code)->exists());

        return $code;
    }

    public function store(AopApplicationRequest $request)
    {

        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            $curr_user = User::find($request->user()->id);
            $curr_user_authorization_pin = $curr_user->authorization_pin;

            if ($curr_user_authorization_pin !== $request->authorization_pin) {
                return response()->json([
                    'message' => 'Invalid Authorization Pin'
                ], Response::HTTP_BAD_REQUEST);
            }
            $user_id = $request->user()->id;
            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();
            $existingAop = AopApplication::where('sector', $area['sector'])
                ->where('sector_id', $area['details']['id'])
                ->first();

            if ($existingAop) {
                return response()->json([
                    'message' => 'You already have an AOP application in your area.',
                ], 200);
            }

            switch ($area['sector']) {
                case 'Division':
                    $division = Division::where('name', $area['details']['name'])->first();
                    $divisionChiefId = $division->head_id;
                    break;
                case 'Department':
                    $department = Department::where('name', $area['details']['name'])->first();
                    $division = Division::where('id', $department->division_id)->first();
                    $divisionChiefId = $division->head_id;
                    break;
                case 'Section':
                    $section = Section::where('name', $area['details']['name'])->first();
                    if ($section?->department_id) {
                        $department = Department::find($section->department_id);
                        $division = Division::find($department?->division_id);
                    } else {
                        $division = Division::find($section?->division_id);
                    }

                    $divisionChiefId = $division?->head_id ?? null;

                    break;
                case 'Unit':
                    $unit = Unit::where('name', $area['details']['name'])->first();
                    $section = Section::where('id', $unit->section_id)->first();

                    if ($section?->department_id) {
                        $department = Department::find($section->department_id);
                        $division = Division::find($department?->division_id);
                    } else {

                        $division = Division::find($section?->division_id);
                    }
                    $divisionChiefId = $division?->head_id ?? null;
                    break;
            }
            $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
            $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

            if (is_null($mccChiefId)) {
                return response()->json(['message' => 'Medical Center Chief not found.'], 404);
            }


            $planningOfficer = Section::where('name', 'Planning Unit')->first();
            $planningOfficerId = optional($planningOfficer)->head_id;


            if (is_null($planningOfficerId)) {
                return response()->json(['message' => 'Planning Officer not found.'], 404);
            }

            $budgetOfficer = Section::where('name', 'FS: Budget Section')->first();
            $budgetOfficerId = optional($budgetOfficer)->head_id;


            if (is_null($budgetOfficerId)) {
                return response()->json(['message' => 'Budget Officer not found.'], 404);
            }



            // Create AOP Application
            $aopApplication = AopApplication::create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'mcc_chief_id' => $mccChiefId,
                'planning_officer_id' => $planningOfficerId,
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission'],
                'status' => $validatedData['status'],
                'sector' => $area['sector'],
                'sector_id' => $area['details']['id'],
                'has_discussed' => $validatedData['has_discussed'],
                'remarks' => $validatedData['remarks'] ?? null,
                'year' => now()->year + 1,
            ]);


            foreach ($validatedData['application_objectives'] as $objectiveData) {
                // $functionObjective = FunctionObjective::where('objective_id', $objectiveData['objective_id'])
                //     ->where('function_id', $objectiveData['function'])
                //     ->first();

                // if (!$functionObjective) {

                //     return response()->json(['error' => 'FunctionObjective not found'], 404);
                // }


                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'objective_id' => $objectiveData['objective_id'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                ]);


                if ($applicationObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->otherObjective()->create([
                        'description' => $objectiveData['others_objective'],
                    ]);
                }

                $successIndicator = SuccessIndicator::find($objectiveData['success_indicator_id']);

                if (
                    $successIndicator &&
                    $successIndicator->description === 'Others' &&
                    isset($objectiveData['other_success_indicator'])
                ) {
                    $applicationObjective->otherSuccessIndicator()->create([
                        'description' => $objectiveData['other_success_indicator'],
                    ]);
                }

                foreach ($objectiveData['activities'] as $activityData) {
                    $activity = $applicationObjective->activities()->create([
                        'activity_code' => $this->generateUniqueActivityCode(),
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);


                    $activity->resources()->createMany($activityData['resources']);


                    $activity->responsiblePeople()->createMany($activityData['responsible_people']);
                }
            }

            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');

            $ppmpTotal = 0;

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if (
                            $resource->purchase_type_id === $procurablePurchaseTypeId &&
                            $resource->item
                        ) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'ppmp_application_id' => null,
                'action' => "Create Aop",
                'action_by' => $user_id,
            ]);


            $ppmpApplication = $aopApplication->ppmpApplication()->create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'budget_officer_id' => $budgetOfficerId,
                'planning_officer_id' => $planningOfficerId,
                'ppmp_application_uuid' => Str::uuid(),
                'ppmp_total' => $ppmpTotal,
                'status' => $validatedData['status'],
            ]);


            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if (
                            $resource->purchase_type_id === $procurablePurchaseTypeId &&
                            $resource->item // Check if item exists
                        ) {
                            $estimatedBudget = $resource->item->estimated_budget;
                            $totalAmount = $resource->quantity * $estimatedBudget;

                            PpmpItem::create([
                                'ppmp_application_id' => $ppmpApplication->id,
                                'item_id' => $resource->item_id,
                                'total_quantity' => $resource->quantity,
                                'estimated_budget' => $estimatedBudget,
                                'expense_class' => $resource->expense_class, //by ricah
                                'total_amount' => $totalAmount,
                                'remarks' => null,
                            ]);
                        }
                    }
                }
            }

            Log::create([
                'aop_application_id' => null,
                'ppmp_application_id' => $ppmpApplication->id,
                'action' => "Create Ppmp",
                'action_by' => $user_id,
            ]);

            if ($request->status !== 'draft') {

                // Get the aop user and its area
                $aop_user = User::find($aopApplication->user_id);
                $aop_user_assigned_area = $aop_user->assignedArea;
                $aop_user_authorization_pin = $aop_user->authorization_pin;

                // Get the approval service
                $approval_service = new ApprovalService($this->notificationService);

                // Create an initial timeline entry using the specialized method
                $aop_application_timeline = $approval_service->createInitialApplicationTimeline(
                    $aopApplication,
                    $curr_user,
                    $request->remarks
                );

                if (!$aop_application_timeline || (is_array($aop_application_timeline) && !($aop_application_timeline['success'] ?? true))) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create AOP application timeline',
                        'details' => is_array($aop_application_timeline) ? ($aop_application_timeline['error'] ?? 'Unknown error') : 'Unknown error'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            DB::commit();

            return response()->json(['message' => 'AOP Application created successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(), // optional: useful for debugging
            ], 500);
        }
    }

    public function show($id)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments',
        ])->findOrFail($id);

        foreach ($aopApplication->applicationObjectives as $objective) {
            foreach ($objective->activities as $activity) {
                foreach ($activity->responsiblePeople as $responsiblePerson) {
                    $responsiblePerson->resolved_name = $this->getResponsiblePersonName($responsiblePerson);
                }
            }
        }

        return new AopApplicationResource($aopApplication);
    }

    public function showWithComments($id)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments', // Include activity comments
        ])->findOrFail($id);

        return new AopApplicationResource($aopApplication);
    }

    public function getUsersWithDesignation()
    {
        $users = User::with('designation')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => $user->name,
                    'designation' => $user->designation?->name
                ];
            });

        return response()->json($users);
    }

    public function getAllDesignations()
    {
        $designations = Designation::all();

        return DesignationResource::collection($designations);
    }

    public function getAllArea()
    {
        $divisions = Division::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'division';
            $item->label = $item->name;
            return $item;
        });

        $departments = Department::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'department';
            $item->label = $item->name;
            return $item;
        });

        $sections = Section::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'section';
            $item->label = $item->name;
            return $item;
        });

        $units = Unit::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'unit';
            $item->label = $item->name;
            return $item;
        });

        $all = $divisions
            ->concat($departments)
            ->concat($sections)
            ->concat($units)
            ->values();

        return response()->json($all);
    }

    public function destroy(AopApplication $aopApplication)
    {
        //
    }

    public function aopRequests(Request $request): JsonResponse
    {
        $visibilityService = new AopVisibilityService();
        $filters = $request->only(['status', 'year', 'search']);

        $aopApplications = $visibilityService->getAopApplications($request->user(), $filters)
            ->orderBy('updated_at', 'desc')
            ->get();

        $metadata = $this->getMetadata('getAopApplications');

        return response()->json([
            'metadata' => $metadata,
            'data' => AopRequestResource::collection($aopApplications),
        ]);
    }


    /**
     * Process an AOP application request through the approval workflow
     *
     * @param ProcessAopRequest $request The incoming request
     * @return \Illuminate\Http\JsonResponse JSON response
     * @throws \Exception
     */
    public function processAopRequest(ProcessAopRequest $request): JsonResponse
    {
        $aop_application = AopApplication::with([
            'applicationObjectives',
            'applicationTimelines',
            'user'
        ])
            ->where('id', $request->aop_application_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$aop_application) {
            return response()->json([
                'message' => 'AOP Application not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Get the current user and its area
        $curr_user = User::find($request->user()->id);
        $curr_user_assigned_area = $curr_user->assignedArea;
        $curr_user_authorization_pin = $curr_user->authorization_pin;

        // Get the aop user and its area
        $aop_user = User::find($aop_application->user_id);

        $aop_user_assigned_area = $aop_user->assignedArea;


        if ($curr_user_authorization_pin !== $request->authorization_pin) {
            return response()->json([
                'message' => 'Invalid Authorization Pin'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Use ApprovalService to process the request
        $approval_service = new ApprovalService($this->notificationService);

        // Create a timeline entry using the service
        $aop_application_timeline = $approval_service->createApplicationTimeline(
            $aop_application,
            $curr_user,
            $aop_user,
            $request->status,
            $request->remarks
        );

        if (!$aop_application_timeline) {
            return response()->json([
                'message' => 'AOP application timeline not created',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Update the AOP application status based on the workflow stage
        $statusMap = [
            'approved' => 'approved',
            'returned' => 'returned',
            // Add other status mappings as needed
        ];

        if (isset($statusMap[$request->status])) {
            $aop_application->status = $statusMap[$request->status];
            $aop_application->save();
        }

        // Get the current user information
        $user_info = $curr_user_assigned_area->user;

        // Get the next office information based on the updated application's current area
        // This is set in the workflow service during timeline creation
        $next_area_info = null;

        $application_timeline = ApplicationTimeline::where('aop_application_id', $aop_application->id)->latest()->first();

        // Notifications are now handled in ApprovalService

        // Build detailed success response
        $current_area_info = new AssignedAreaResource($curr_user_assigned_area);

        $status_text = ucfirst($request->status);

        $response_message = "AOP application {$status_text}";
        if ($request->status === 'approved') {
            $response_message .= " and forwarded to next step";
        } elseif ($request->status === 'returned') {
            $response_message .= " and sent back for revision";
        }

        return response()->json([
            'success' => true,
            'message' => $response_message,
            'data' => [
                'timeline' => $aop_application_timeline,
                'status_details' => [
                    'status' => $aop_application->status,
                    'current_area_info' => $current_area_info,
                    'next_area_info' => $next_area_info,
                    'date_updated' => now()->format('Y-m-d H:i:s'),
                    'remarks' => $request->remarks
                ]
            ]
        ], Response::HTTP_OK);
    }
    public function export($id)
    {

        $user_id = 2;
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();
        $area = $assignedArea->findDetails();
        $userArea = $area['details']['name'];
        switch ($area['sector']) {
            case 'Division':
                $division = Division::where('name', $area['details']['name'])->first();
                $divisionChiefId = $division->head_id;
                $headId = $division->head_id;
                break;
            case 'Department':
                $department = Department::where('name', $area['details']['name'])->first();
                $headId = $department->head_id;
                $division = Division::where('id', $department->division_id)->first();
                $divisionChiefId = $division->head_id;
                break;
            case 'Section':
                $section = Section::where('name', $area['details']['name'])->first();
                $headId = $section->head_id;
                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {
                    $division = Division::find($section?->division_id);
                }

                $divisionChiefId = $division?->head_id ?? null;
                break;
            case 'Unit':
                $unit = Unit::where('name', $area['details']['name'])->first();
                $headId = $unit->head_id;
                $section = Section::where('id', $unit->section_id)->first();

                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {

                    $division = Division::find($section?->division_id);
                }
                $divisionChiefId = $division?->head_id ?? null;
                break;
        }

        $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
        $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

        $planningOfficer = Section::where('name', 'Planning Unit')->first();
        $planningOfficerId = optional($planningOfficer)->head_id;

        $head = \App\Models\User::find($headId);
        $preparedByName = $head?->name ?? 'N/A';
        $divisionHead = \App\Models\User::find($divisionChiefId);
        $notedBy = $divisionHead?->name ?? 'N/A';
        $mccChief = \App\Models\User::find($mccChiefId);
        $ApprovedBy = $mccChief?->name ?? 'N/A';
        $planning = \App\Models\User::find($planningOfficerId);
        $ReviewedBy = $planning?->name ?? 'N/A';

        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
        ])->findOrFail($id);

        $getResponsiblePersonName = function ($responsiblePerson) {
            if ($responsiblePerson->user_id) {
                return $responsiblePerson->user->name;
            }

            if ($responsiblePerson->division_id) {
                return $responsiblePerson->division->name;
            }
            if ($responsiblePerson->department_id) {
                return $responsiblePerson->department->name;
            }
            if ($responsiblePerson->section_id) {
                return $responsiblePerson->section->name;
            }
            if ($responsiblePerson->unit_id) {
                return $responsiblePerson->unit->name;
            }

            return 'Unknown';
        };

        // Prepare the objectives and activities data
        $objectives = $aopApplication->applicationObjectives->map(function ($objective) use ($getResponsiblePersonName, $aopApplication) {
            return $objective->activities->map(function ($activity) use ($objective, $getResponsiblePersonName, $aopApplication) {
                // Process resources
                $resources = $activity->resources->map(function ($resource) {
                    $quantity = $resource->quantity ?? '';
                    $itemName = $resource->item->name ?? '';
                    return "{$quantity} {$itemName}";
                })->filter()->implode("\n");

                // Process responsible people
                $responsiblePeople = $activity->responsiblePeople->map(function ($responsible) use ($getResponsiblePersonName) {
                    return $getResponsiblePersonName($responsible); // Get the name
                })->filter()->implode("\n");

                $expenseClasses = $activity->resources->map(function ($resource) {
                    return $resource->expense_class ?? '';
                })->filter()->implode("\n");

                return [
                    'mission' => $aopApplication->mission,
                    'type_of_function' => $objective->objective->typeOfFunction->type ?? '',
                    'objective' => ($objective->objective->description === 'Others')
                        ? ($objective->otherObjective->description ?? '')
                        : ($objective->objective->description ?? ''),

                    'success_indicator' => ($objective->successIndicator->description === 'Others')
                        ? ($objective->otherSuccessIndicator->description ?? '')
                        : ($objective->successIndicator->description ?? ''),
                    'activity_name' => $activity->name,
                    'start_month' => $activity->start_month ? \Carbon\Carbon::parse($activity->start_month)->format('F') : '',
                    'end_month' => $activity->end_month ? \Carbon\Carbon::parse($activity->end_month)->format('F') : '',
                    'target_q1' => $activity->target->first_quarter ?? '',
                    'target_q2' => $activity->target->second_quarter ?? '',
                    'target_q3' => $activity->target->third_quarter ?? '',
                    'target_q4' => $activity->target->fourth_quarter ?? '',
                    'resources' => $resources,
                    'cost' => $activity->cost ?? 0,
                    'expense_class' => $expenseClasses,
                    'responsible_people' => $responsiblePeople,
                    'is_gad_related' => $activity->is_gad_related,
                ];
            });
        });



        // Load template
        $templatePath = storage_path('app/template/operational_plan.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B8', $aopApplication->mission);
        $sheet->setCellValue('D7', $area['details']['name']);

        $defaultTableStart = 14; // Starting row of the table
        $defaultTableRows = 30;
        $totalDataRows = $objectives->flatten(1)->count();

        if ($totalDataRows > $defaultTableRows) {
            $rowsToAdd = $totalDataRows - $defaultTableRows;

            // Insert new rows after the last default row (row 14 + 30 = 44)
            $insertPosition = $defaultTableStart + $defaultTableRows;

            $sheet->insertNewRowBefore($insertPosition + 1, $rowsToAdd);

            // Optional: Copy style from the last default row to new rows
            $templateRow = $insertPosition;
            for ($i = 1; $i <= $rowsToAdd; $i++) {
                $sourceRow = $templateRow;
                $targetRow = $templateRow + $i;

                for ($col = 'A'; $col <= 'O'; $col++) {
                    $style = $sheet->getStyle("{$col}{$sourceRow}");
                    $sheet->duplicateStyle($style, "{$col}{$targetRow}");
                }
            }
        }

        // Start populating from row 14
        $row = 14;
        foreach ($objectives as $objectiveActivities) {
            foreach ($objectiveActivities as $item) {
                // Set the data in the cells
                $sheet->setCellValue("A{$row}", $item['type_of_function']);
                $sheet->setCellValue("B{$row}", $item['objective']);
                $sheet->setCellValue("C{$row}", $item['success_indicator']);
                $sheet->setCellValue("D{$row}", $item['activity_name']);
                $sheet->setCellValue("E{$row}", $item['start_month']);
                $sheet->setCellValue("F{$row}", $item['end_month']);
                $sheet->setCellValue("G{$row}", $item['target_q1']);
                $sheet->setCellValue("G{$row}", $item['target_q1']);
                $sheet->setCellValue("H{$row}", $item['target_q2']);
                $sheet->setCellValue("I{$row}", $item['target_q3']);
                $sheet->setCellValue("J{$row}", $item['target_q4']);
                $sheet->setCellValue("K{$row}", $item['resources']);
                $sheet->setCellValue("L{$row}", $item['cost']);
                $sheet->setCellValue("M{$row}", $item['expense_class']);
                $sheet->setCellValue("N{$row}", $item['responsible_people']);
                $sheet->setCellValue("O{$row}", $item['is_gad_related']);
                $row++;
            }
        }

        $lastDataRow = $row - 1; // Last populated row with actual data

        // Find the start of the footer/signature section
        $footerStartRow = null;
        $maxScanRow = $sheet->getHighestRow();

        for ($i = $lastDataRow + 1; $i <= $maxScanRow; $i++) {
            $cellB = $sheet->getCell("B{$i}")->getValue();
            $cellD = $sheet->getCell("D{$i}")->getValue();
            $cellK = $sheet->getCell("K{$i}")->getValue();
            $cellM = $sheet->getCell("M{$i}")->getValue();

            if (
                stripos($cellB, 'Prepared by') !== false ||
                stripos($cellD, 'Noted by') !== false ||
                stripos($cellK, 'Reviewed by') !== false ||
                stripos($cellM, 'Approved by') !== false
            ) {
                $footerStartRow = $i;
                break;
            }
        }

        // Delete empty rows between data and footer, but keep 4 clean rows
        $rowsToKeep = 4;
        if ($footerStartRow && $footerStartRow > $lastDataRow + $rowsToKeep) {
            $numRowsToDelete = $footerStartRow - $lastDataRow - $rowsToKeep;
            $sheet->removeRow($lastDataRow + 1, $numRowsToDelete);
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("B{$i}")->getValue();
            if (stripos($cellValue, 'Prepared by') !== false) {
                $sheet->setCellValue("B" . ($i + 2), $preparedByName);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("D{$i}")->getValue();
            if (stripos($cellValue, 'Noted by:') !== false) {
                $sheet->setCellValue("D" . ($i + 2), $notedBy);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("K{$i}")->getValue();
            if (stripos($cellValue, 'Reviewed by:') !== false) {
                $sheet->setCellValue("K" . ($i + 2), $ReviewedBy);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("M{$i}")->getValue();
            if (stripos($cellValue, 'Approved by:') !== false) {
                $sheet->setCellValue("M" . ($i + 2), $ApprovedBy);
                break;
            }
        }

        $lastRow = $row - 1;
        $sheet->getStyle("G14:J{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle("L14:L{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        $tempDirectory = storage_path('app/temp');
        if (!File::exists($tempDirectory)) {
            File::makeDirectory($tempDirectory, 0777, true); // Create directory if it doesn't exist
        }

        // Output the modified file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'aop_application_' . $id . '.xlsx';
        $tempPath = storage_path("app/temp/{$fileName}");
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    // Helper method to get the formatted objective description
    public function getObjectiveDescription($objective)
    {
        $description = $objective->objective->description ?? null;
        if ($description === 'Others' && $objective->othersObjective) {
            return $description . ': ' . $objective->othersObjective->description;
        }
        return $description;
    }

    // Helper method to get the formatted success indicator description
    public function getSuccessIndicatorDescription($objective)
    {
        $description = $objective->successIndicator->description ?? null;
        if ($description === 'Others' && $objective->otherSuccessIndicator) {
            return $description . ': ' . $objective->otherSuccessIndicator->description;
        }
        return $description;
    }

    private function getResponsiblePersonName($responsiblePerson)
    {

        if ($responsiblePerson->user_id) {
            return $responsiblePerson->user->name;
        }

        if ($responsiblePerson->division_id) {
            return $responsiblePerson->division->name;
        }
        if ($responsiblePerson->department_id) {
            return $responsiblePerson->department->name;
        }
        if ($responsiblePerson->section_id) {
            return $responsiblePerson->section->name;
        }
        if ($responsiblePerson->unit_id) {
            return $responsiblePerson->unit->name;
        }

        if ($responsiblePerson->designation_id) {
            return $responsiblePerson->designation->name;
        }

        return 'Unknown';
    }

    /**
     * This function is used to get the remarks per AOP application or request
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * Last edited by: Micah Mustaham, Updated by: Cascade
     */
    public function aopRemarks($id): JsonResponse
    {
        if (!$id) {
            return response()->json([
                'message' => 'AOP Application ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get the specific AOP application with user and divisionChief relationships
        $aopApplication = AopApplication::with([
            'user.assignedArea.designation',
            'divisionChief.assignedArea.designation'
        ])->find($id);

        if (!$aopApplication) {
            return response()->json([
                'message' => 'AOP Application not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if there are remarks for this application
        if (!$aopApplication->remarks) {
            return response()->json([
                'message' => 'No remarks found for this AOP application'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "message" => "Remarks retrieved successfully",
            "data" => new AopRemarksResource($aopApplication),
            "metadata" => $this->getMetadata('get')
        ], Response::HTTP_OK);
    }

    public function edit()
    {
        $aop_application = AopApplication::with([
            'applicationObjectives' => function ($query) {
                $query->with([
                    'activities',
                    'activities.target',
                    'activities.resources',
                    'activities.resources.item',
                    'activities.resources.purchaseType',
                    'activities.responsiblePeople',
                    'activities.responsiblePeople.user',
                    'activities.responsiblePeople.division',
                    'activities.responsiblePeople.department',
                    'activities.responsiblePeople.section',
                    'activities.responsiblePeople.unit',
                    'activities.responsiblePeople.designation',
                    'activities.comments',
                    'objective',
                    'otherObjective',
                    'objective.successIndicators',
                    'objective.typeOfFunction',
                    'objective.typeOfFunction.objectives',
                    'objective.typeOfFunction.objectives.successIndicators',
                    'successIndicator',
                    'otherSuccessIndicator',
                ]);
            }
        ])->latest()->first();

        if (!$aop_application) {
            return response()->json([
                'message' => 'AOP Application not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new AopResource($aop_application),
            'message' => 'AOP Application retrieved successfully'
        ], Response::HTTP_OK);
    }
}
