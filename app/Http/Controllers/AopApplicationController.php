<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\AopRemarksResource;
use App\Http\Resources\AopRequestResource;
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
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Services\ApprovalWorkflowService;
use App\Services\NotificationService;
use App\Services\AopVisibilityService;

class AopApplicationController extends Controller
{
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

        return $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.objective',
                'applicationObjectives.otherObjective',
                'applicationObjectives.successIndicator',
                'applicationObjectives.otherSuccessIndicator',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.responsiblePeople.user',
            ])
            ->get();

        return AopApplicationResource::collection($aopApplications);
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

    public function showTimeline($aopApplicationId)
    {
        $aopApplication = AopApplication::with('applicationTimelines')
            ->findOrFail($aopApplicationId);

        return ApplicationTimelineResource::collection(
            $aopApplication->applicationTimelines
        );
    }

    public function update(AopApplicationRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $aopApplication = AopApplication::with([
                'applicationObjectives.activities.resources.item',
                'ppmpApplication',
            ])->findOrFail($id);


            $aopApplication->update($request->only([
                'user_id',
                'division_chief_id',
                'mcc_chief_id',
                'planning_officer_id',
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


            $aopApplication->ppmpApplication->ppmpItems()->delete();

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $estimatedBudget = $resource->item->estimated_budget;
                            $totalAmount = $resource->quantity * $estimatedBudget;

                            $aopApplication->ppmpApplication->ppmpItems()->create([
                                'item_id' => $resource->item_id,
                                'total_quantity' => $resource->quantity,
                                'estimated_budget' => $estimatedBudget,
                                'total_amount' => $totalAmount,
                                'remarks' => null,
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'AOP Application updated successfully.']);
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

            $assignedArea = AssignedArea::with('division')->where('user_id', $validatedData['user_id'])->first();
            $divisionChiefId = optional($assignedArea->division)->head_id;

            $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
            $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

            $planningOfficer = Section::where('name', 'OMCC: Planning Unit')->first();
            $planningOfficerId = optional($planningOfficer)->head_id;





            // Create AOP Application
            $aopApplication = AopApplication::create([
                'user_id' => $validatedData['user_id'],
                'division_chief_id' => $divisionChiefId,
                'mcc_chief_id' => $mccChiefId,
                'planning_officer_id' => $planningOfficerId,
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission'],
                'status' => $validatedData['status'],
                'has_discussed' => $validatedData['has_discussed'],
                'remarks' => $validatedData['remarks'] ?? null,
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

            $assignedArea = AssignedArea::with('division')->where('user_id', $validatedData['user_id'])->first();
            $divisionChiefId = optional($assignedArea->division)->head_id;

            $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
            $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

            // $budgetOfficer = Section::where('name', 'FS: Budget Section')->first();
            // $budgetOfficerId = optional($budgetOfficer)->head_id;


            $ppmpApplication = $aopApplication->ppmpApplication()->create([
                'user_id' => $validatedData['user_id'],
                'division_chief_id' => $divisionChiefId,
                'budget_officer_id' => 1,
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
                                'total_amount' => $totalAmount,
                                'remarks' => null,
                            ]);
                        }
                    }
                }
            }

            $aopApplicationTimeline = $aopApplication->applicationTimelines()->create([
                'aop_application_id' => $aopApplication->id,
                'user_id' => 1,
                'current_area_id' => 1,
                'next_area_id' => 2,
                'status' => $validatedData['status'],
                'date_created' => now(),
            ]);
            DB::commit();

            return response()->json(['message' => 'AOP Application created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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
                    // Check and resolve the name
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

    public function aopRequests(Request $request)
    {
        $page = $request->query('page') > 0 ? $request->query('page') : 1;
        $per_page = $request->query('per_page') ?? 15;

        // Get current authenticated user
        $user = User::find($request->user()->id);
        $assignedArea = $user->assignedArea;

        if (!$assignedArea) {
            return response()->json([
                'message' => 'User does not have an assigned area',
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $per_page,
                    'current_page' => $page,
                    'last_page' => 1,
                ],
                'metadata' => $this->getMetadata('getAopApplications')
            ], Response::HTTP_OK);
        }

        // Use the AOP visibility service to get applications user can see
        $aopVisibilityService = new AopVisibilityService();

        $filters = [
            'status' => $request->has('status') ? $request->status : null,
            'year' => $request->has('year') ? $request->year : null,
            'search' => $request->has('search') ? $request->search : null,
        ];

        // Get query from visibility service with proper permissions and filters already applied
        $query = $aopVisibilityService->getVisibleAopApplications($user, $filters);

        // Paginate the results
        $aopApplications = $query->paginate($per_page, ['*'], 'page', $page);

        return response()->json([
            'data' => AopRequestResource::collection($aopApplications),
            'pagination' => [
                'total' => $aopApplications->total(),
                'per_page' => $aopApplications->perPage(),
                'current_page' => $aopApplications->currentPage(),
                'last_page' => $aopApplications->lastPage(),
            ],
            'metadata' => $this->getMetadata('getAopApplications')
        ], Response::HTTP_OK);
    }


    /**
     * Process an AOP application request through the approval workflow
     *
     * @param Request $request The incoming request
     * @return mixed JSON response
     */
    public function processAopRequest(Request $request): mixed
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated user',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user_id = $request->user()->id;

        $validated = Validator::make($request->all(), [
            'aop_application_id' => 'required|integer',
            'status' => 'required|string|',
            'remarks' => 'nullable|string|max:500',
            'auth_pin' => 'required|integer|digits:6',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

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

        // Get the current user's assigned area
        $current_user_area = AssignedArea::with([
            'department',
            'section',
            'unit',
            'division',
            'user'
        ])->where('user_id', $user_id)->first();

        if (!$current_user_area) {
            return response()->json([
                'message' => 'User does not have an assigned area',
            ], Response::HTTP_NOT_FOUND);
        }

        // Use ApprovalWorkflowService to process the request
        $workflow_service = new ApprovalWorkflowService();

        // Create a timeline entry using the service
        $aop_application_timeline = $workflow_service->createApplicationTimeline(
            $aop_application->id,
            $user_id,
            $current_user_area->id,
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
        $user_info = $current_user_area->user;

        // Get the next office information based on the updated application's current area
        // This is set in the workflow service during timeline creation
        $next_area_info = null;

        $application_timeline = ApplicationTimeline::where('aop_application_id', $aop_application->id)->latest()->first();


        if ($application_timeline) {
            $next_area = AssignedArea::with(['department', 'section', 'unit', 'division'])
                ->find($application_timeline->next_area_id);
            if ($next_area) {
                $next_area_info = new AssignedAreaResource($next_area);
            }
        }

        // Build detailed success response
        $current_area_info = new AssignedAreaResource($current_user_area);

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
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople.user',
        ])->findOrFail($id);

        $getResponsiblePersonName = function ($responsiblePerson) {
            if ($responsiblePerson->user_id) {
                return $responsiblePerson->user->name;  // Assuming you have a 'user' relationship in the model
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
    public function aopRemarks($id)
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
}
