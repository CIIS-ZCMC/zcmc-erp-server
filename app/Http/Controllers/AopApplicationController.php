<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\ShowAopApplicationResource;
use App\Http\Resources\AopRequestResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Http\Resources\DesignationResource;
use App\Models\AopApplication;
use App\Models\AssignedArea;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\FunctionObjective;
use App\Models\PpmpItem;
use App\Models\PurchaseType;
use App\Models\Section;
use App\Models\SuccessIndicator;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use PhpOffice\PhpSpreadsheet\IOFactory;


#[OA\Schema(
    schema: "AopApplication",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "activity_id", type: "integer"),
        new OA\Property(property: "user_id", type: "integer", nullable: true),
        new OA\Property(property: "content", type: "string"),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time"
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time"
        )
    ]
)]

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
            'total_objectives'          => $totalObjectives,
            'total_success_indicators'  => $totalSuccessIndicators,
            'total_activities'          => $totalActivities,
            'total_gad_related'         => $totalGadRelated,
            'total_not_gad_related'     => $totalNotGadRelated,
            'total_cost'                => $totalActivityCost,
            'total_resources'           => $totalResources,
            'total_comments'            => $totalComments,
            'total_responsible_people'  => $totalResponsiblePeople->count(),
            'total_areas'               => $totalAreas,
            'total_job_positions'       => $totalJobPositions,
            'total_users'               => $totalUsers,
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
                        'expense_class' => $activityData['expense_class'],
                        'activity_code' => $activityData['activity_code'],
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
                    'objective_id'         => $objectiveData['objective_id'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                ]);


                if ($applicationObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->othersObjective()->create([
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
                        'expense_class' => $activityData['expense_class'],
                        'activity_code' => $activityData['activity_code'],
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
                                'item_id'             => $resource->item_id,
                                'total_quantity'      => $resource->quantity,
                                'estimated_budget'    => $estimatedBudget,
                                'total_amount'        => $totalAmount,
                                'remarks'             => null,
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

    #[OA\Get(
        path: "/api/aop-applications/{id}",
        summary: "Show specific activity comment",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Comment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            )
        ]
    )]
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
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'label'       =>  $user->name,
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



    #[OA\Put(
        path: "/api/aop-applications/{id}",
        summary: "Update an activity comment",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Comment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Comment data",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Comment updated",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]


    #[OA\Delete(
        path: "/api/aop-applications/{id}",
        summary: "Delete an activity comment",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Comment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Comment deleted"
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            )
        ]
    )]
    public function destroy(AopApplication $aopApplication)
    {
        //
    }

    #[OA\Get(
        path: "/api/aop-requests",
        summary: "List all AOP requests",
        tags: ["AOP Requests"],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filter by status",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "year",
                in: "query",
                description: "Filter by year",
                required: false,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/AopRequest")
                )
            )
        ]
    )]
    public function listOfAopRequests(Request $request)
    {
        $page = $request->query('page') > 0 ? $request->query('page') : 1;
        $per_page = $request->query('per_page') ?? 15;

        $query = AopApplication::with([
            'user',
            'applicationTimeline',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('year')) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mission', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

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


    public function processAopRequest(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'aop_application_id' => 'required|integer',
            'status' => 'required|string|',
            'remarks' => 'nullable|string|max:500',
            'auth_pin' => 'required|integer|digits:6',
        ]);

        $user_id = 2398;

        // Route for the timeline
        $applicationTimelineRoute = [
            'Planning Unit', // Head of Planning Unit
            'Division Chief', // Heads of Division offices
            'Medical Center Chief', // Chief of OMCC
            'Budget Officer', // Head of Budget
        ];

        switch ($request->status) {
            case 'approved':
                $nextArea = $applicationTimelineRoute[1];
                break;
            case 'returned':
                $nextArea = $applicationTimelineRoute[0];
                break;
            default:
                $nextArea = $applicationTimelineRoute[0];
        }

        return $nextArea;

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $aopApplication = AopApplication::with([
            'applicationObjectives',
            'applicationTimeline',
            'user'
        ])
            ->where('id', $request->aop_application_id)
            ->whereNull('deleted_at')
            ->first();

        $dateApproved = null;
        $dateReturned = null;

        /*
        * Later, add in the logic of determining what's the next office base on the status.
        */
        $status = match ($request->status) {
            'approved' => $dateApproved = now(),
            'returned' => $dateReturned = now(),
            default => null,
        };

        $aopApplicationTimeline = $aopApplication->applicationTimeline()->create([
            'aop_application_id' => $request->aop_application_id,
            'user_id' => $user_id,
            'current_area_id' => 1,
            'next_area_id' => 2,
            'status' => $status,
            'remarks' => $request->remarks,
            'date_created' => now(),
            'date_approved' => $dateApproved,
            'date_returned' => $dateReturned,
        ]);

        if (!$aopApplicationTimeline) {
            return response()->json([
                'message' => 'AOP application timeline not created',
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'AOP application processed successfully',
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

        $objectives = $aopApplication->applicationObjectives->map(function ($objective) {
            return $objective->activities->map(function ($activity) use ($objective) {
                $resources = $activity->resources->map(function ($resource) {
                    $quantity = $resource->quantity ?? '';
                    $itemName = $resource->item->name ?? '';
                    return "{$quantity} {$itemName}";
                })->filter()->implode(', ');
                return [
                    'type_of_function' => $objective->objective->typeOfFunction->type ?? '',
                    'objective' => $objective->objective->description ?? '',
                    'success_indicator' => $objective->successIndicator->description ?? '',
                    'activity_name' => $activity->name,
                    'start_month' => $activity->start_month ? \Carbon\Carbon::parse($activity->start_month)->format('F') : '',
                    'end_month' => $activity->end_month ? \Carbon\Carbon::parse($activity->end_month)->format('F') : '',
                    'target_q1' => $activity->target->first_quarter ?? '',
                    'target_q2' => $activity->target->second_quarter ?? '',
                    'target_q3' => $activity->target->third_quarter ?? '',
                    'target_q4' => $activity->target->fourth_quarter ?? '',
                    'resources' => $resources,
                    'cost' => $activity->cost ?? 0,
                    'expense_class' => $activity->expense_class,
                ];
            });
        });


        // Load template
        $templatePath = storage_path('app/template/operational_plan.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

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
}
