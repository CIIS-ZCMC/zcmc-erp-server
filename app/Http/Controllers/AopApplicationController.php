<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\ShowAopApplicationResource;
use App\Http\Resources\AopRequestResource;
use App\Models\AopApplication;
use App\Models\FunctionObjective;
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
    private $is_development;
    private $module = 'aop-applications';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata = ["methods" => ["GET, POST, PUT, DELETE"]];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?item_category_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value"
                ];
            }

            return $metadata;
        }

        if ($method === 'put') {
            $metadata = ["methods" => ["PUT"]];

            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2"
                ];
                $metadata['fields'] = ["name", "code"];
            }

            return $metadata;
        }

        $metadata = ["methods" => ["GET, PUT, DELETE"]];

        if ($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];
            $metadata['fields'] = ["code"];
        }

        return $metadata;
    }

    #[OA\Get(
        path: "/api/aop-applications",
        summary: "List all activity comments",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Items per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/ActivityComment")
                )
            )
        ]
    )]
    public function index()
    {
        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.functionObjective.function',
                'applicationObjectives.functionObjective.objective',
                'applicationObjectives.otherObjective',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.responsiblePeople.user'
            ])
            ->get();
        return AopApplicationResource::collection($aopApplications);
    }

    #[OA\Post(
        path: "/api/aop-applications",
        summary: "Create a new activity comment",
        tags: ["Activity Comments"],
        requestBody: new OA\RequestBody(
            description: "Comment data",
            required: true,
            content: new OA\JsonContent(
                required: ["activity_id", "content"],
                properties: [
                    new OA\Property(property: "activity_id", type: "integer"),
                    new OA\Property(property: "content", type: "string"),
                    new OA\Property(property: "user_id", type: "integer", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Comment created",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
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


                $functionObjective = FunctionObjective::where('objective_id', $objectiveData['objective_id'])
                    ->where('function_id', $objectiveData['function'])
                    ->first();

                if (!$functionObjective) {

                    return response()->json(['error' => 'FunctionObjective not found'], 404);
                }


                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'function_objective_id' => $functionObjective->id,
                    'objective_code' => $objectiveData['objective_code'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                ]);


                if ($applicationObjective->functionObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->otherObjective()->create([
                        'description' => $objectiveData['others_objective'],
                    ]);
                }

                foreach ($objectiveData['activities'] as $activityData) {
                    $activity = $applicationObjective->activities()->create([
                        'activity_uuid' => $activityData['activity_uuid'],
                        'activity_code' => $activityData['activity_code'],
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        ' ' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);


                    $activity->resources()->createMany($activityData['resources']);


                    $activity->responsiblePeople()->createMany($activityData['responsible_person']);
                }
            }


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
    public function show(AopApplication $aopApplication)
    {
        $aopApplication->load([
            'user',
            'divisionChief',
            'mccChief',
            'planningOfficer',
            'applicationObjectives',
            'applicationObjectives.functionObjective',
            'applicationObjectives.activities'
        ]);

        return new ShowAopApplicationResource($aopApplication);
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
    public function update(Request $request, AopApplication $aopApplication)
    {
        //
    }

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
