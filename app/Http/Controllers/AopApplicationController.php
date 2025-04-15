<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\ShowAopApplicationResource;
use App\Http\Resources\AopRequestResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Models\AopApplication;
use App\Models\FunctionObjective;
use App\Models\PpmpItem;
use App\Models\PurchaseType;
use App\Models\SuccessIndicator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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

            // 1. Update AOP application
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

            // 2. Delete old nested relationships
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

            // 3. Recreate all nested objectives and relations
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
                        'activity_code' => $activityData['activity_code'],
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);

                    // Target
                    $activity->target()->create($activityData['target']);

                    // Resources
                    foreach ($activityData['resources'] as $resourceData) {
                        $activity->resources()->create($resourceData);
                    }

                    // Responsible People
                    foreach ($activityData['responsible_people'] as $personData) {
                        $activity->responsiblePeople()->create($personData);
                    }
                }
            }

            // 4. Recalculate PPMP total
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

            // 5. Delete and rebuild PPMP items
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
            // Create AOP Application
            $aopApplication = AopApplication::create([
                // 'user_id' => $validatedData['user_id'],
                // 'division_chief_id' => $validatedData['division_chief_id'],
                // 'mcc_chief_id' => $validatedData['mcc_chief_id'],
                // 'planning_officer_id' => $validatedData['planning_officer_id'],
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
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            $ppmpApplication = $aopApplication->ppmpApplication()->create([
                'user_id' => 1,
                'division_chief_id' => 1,
                'budget_officer_id' => 1,
                'ppmp_application_uuid' => Str::uuid(),
                'ppmp_total' => $ppmpTotal,
                'status' => 'Pending',
                'remarks' => $request->remarks ?? null,
            ]);


            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
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
                'status' => 'Pending',
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
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments',
        ])->findOrFail($id);

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
            'metadata' => $this->getMetadata('get')
        ], Response::HTTP_OK);
    }


    public function processAopRequest(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'aop_application_id' => 'required|integer|exists:aop_applications,id',
            'status' => 'required|string|',
            'remarks' => 'nullable|string|max:500',
            'auth_pin' => 'required|integer|digits:6',
        ]);

        // NOTE:
        // Get the user who processed the application

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
            'user_id' => $request->user_id,
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
}
