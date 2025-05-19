<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use App\Models\Activity;
use App\Models\ApplicationObjective;
use App\Http\Resources\ManageAopRequestResource;
use App\Http\Resources\ShowObjectiveResource;
use App\Models\SuccessIndicator;
use Illuminate\Support\Facades\DB;
use App\Models\OtherObjective;
use App\Models\OtherSuccessIndicator;
use App\Models\AopApplication;

#[OA\Schema(
    schema: "ActivityComment",
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
class ApplicationObjectiveController extends Controller
{

    #[OA\Get(
        path: "/api/activity-comments",
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
        //
    }

    #[OA\Post(
        path: "/api/activity-comments",
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
    public function store(Request $request)
    {
        //
    }

    #[OA\Get(
        path: "/api/activity-comments/{id}",
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
    public function show(ApplicationObjective $applicationObjective)
    {
        //
    }

    #[OA\Put(
        path: "/api/activity-comments/{id}",
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
    public function update(Request $request, ApplicationObjective $applicationObjective)
    {
        //
    }

    #[OA\Delete(
        path: "/api/activity-comments/{id}",
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
    public function destroy(ApplicationObjective $applicationObjective)
    {
        //
    }

    /**
     * Show a specific AOP request with objectives and activities
     *
     * Retrieves application objectives with their related activities, comments,
     * parent objective, and success indicators for a given AOP application ID.
     *
     * @param int $id The AOP application ID
     * @return \Illuminate\Http\JsonResponse Collection of application objectives with related data
     */
    public function manageAopRequest($id)
    {
        $applicationObjectives = ApplicationObjective::with([
            'activities',
            'activities.comments',
            'objective',
            'otherObjective',
            'objective.typeOfFunction', // Added typeOfFunction relationship
            'successIndicator',
            'otherSuccessIndicator',
        ])
            ->where('aop_application_id', $id)
            ->whereNull('deleted_at')
            ->get();

        if (!$applicationObjectives) {
            return response()->json([
                'message' => 'AOP request not found',
            ], Response::HTTP_NOT_FOUND);
        }


        return response()->json([
            'message' => 'AOP request retrieved successfully',
            'data' => [
                'application' => AopApplication::where('id', $id)->first(),
                'objectives' => ManageAopRequestResource::collection($applicationObjectives)
            ],
        ], Response::HTTP_OK);
    }


    public function showObjectiveActivity($id)
    {
        $activity = Activity::with([
            'comments',
            'target',
            'resources',
            'responsiblePeople',
        ])
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$activity) {
            return response()->json([
                'message' => 'Objective details not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Activity details retrieved successfully',
            'data' => new ShowObjectiveResource($activity),
        ], Response::HTTP_OK);
    }

    public function editObjectiveAndSuccessIndicator(Request $request)
    {
        try {

            $request->validate([
                'aop_application_id' => 'required|exists:aop_applications,id',
                'objective_description' => 'required|string',
                'success_indicator_description' => 'required|string',
            ]);


            // Begin transaction to ensure all operations succeed or fail together
            DB::beginTransaction();

            try {
                // Find the application objective using AopApplication model
                $aopApplication = AopApplication::findOrFail($request->aop_application_id);

                // Get the application objective related to this aop application with specific objective and success indicator IDs
                $applicationObjective = ApplicationObjective::with(['otherObjective', 'otherSuccessIndicator'])
                    ->where('aop_application_id', $aopApplication->id)
                    ->where('objective_id', 23)
                    ->where('success_indicator_id', 36)
                    ->first();

                if (!$applicationObjective) {
                    return response()->json([
                        'message' => 'Application objective not found',
                    ], Response::HTTP_NOT_FOUND);
                }

                // Get or create otherObjective
                $otherObjective = $applicationObjective->otherObjective;

                if (!$otherObjective) {
                    $otherObjective = new OtherObjective([
                        'application_objective_id' => $applicationObjective->id,
                    ]);
                }
                $otherObjective->description = $request->objective_description;
                $otherObjective->save();

                // Get or create otherSuccessIndicator
                $otherSuccessIndicator = $applicationObjective->otherSuccessIndicator;
                if (!$otherSuccessIndicator) {
                    $otherSuccessIndicator = new OtherSuccessIndicator([
                        'application_objective_id' => $applicationObjective->id,
                    ]);
                }
                $otherSuccessIndicator->description = $request->success_indicator_description;
                $otherSuccessIndicator->save();

                // Reload the application objective with its relationships to return
                $applicationObjective->load(['otherObjective', 'otherSuccessIndicator', 'objective', 'successIndicator']);

                // Commit the transaction if all operations succeed
                DB::commit();

                return response()->json([
                    'message' => 'Objective and success indicator updated successfully',
                    'data' => new ManageAopRequestResource($applicationObjective),
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                // Rollback transaction if any operation fails
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Record not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the objective and success indicator',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
