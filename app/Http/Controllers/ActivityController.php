<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\CommentsPerActivityResource;
use App\Models\Activity;
use App\Models\AopApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use User;


#[OA\Schema(
    schema: "Activity",
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
class ActivityController extends Controller
{
    #[OA\Get(
        path: "/api/activities",
        summary: "List all activity comments",
        tags: ["Activity"],
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
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // $request->validate([
        //     'application_objective_id' => 'required|integer',
        // ]);

        $aop = AopApplication::latest()->first();

        // Fetch activities based on the application objective ID
        $data = Activity::with([
            'applicationObjective' => function ($query) use ($aop) {
                $query->where('aop_application_id', $aop->id);
            },
        ])->whereNull(columns: 'deleted_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => ActivityResource::collection($data),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem()
            ],
            'links' => [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl()
            ],
        ], Response::HTTP_OK);
    }

    #[OA\Post(
        path: "/api/activities",
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
        path: "/api/activities/{id}",
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
    public function show(Activity $activity)
    {
        $activity->with([
            'target',
            'resources',
            'responsiblePeople',
            'ppmpItems' => function ($query) {
                $query->whereNull('ppmp_items.deleted_at');
            }
        ])->first();

        return response()->json([
            'data' => new ActivityResource($activity),
            'message' => 'Activity retrieved successfully'
        ], Response::HTTP_OK);
    }

    #[OA\Put(
        path: "/api/activities/{id}",
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
    public function update(Request $request, Activity $activity)
    {
        //
    }

    #[OA\Delete(
        path: "/api/activities/{id}",
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
    public function destroy(Activity $activity)
    {
        //
    }

    public function commentsPerActivity()
    {
        $activity_comments = Activity::with(['commenbts.user'])->paginate(15);

        return response()->json([
            "data" => CommentsPerActivityResource::collection($activity_comments)
        ]);
    }

    /**
     * Mark the specified activity as reviewed.
     *
     * @param int $activity_id
     * @return \Illuminate\Http\JsonResponse
     *
     * Last edited by Micah Mustaham
     */
    public function markAsReviewed($activity_id)
    {
        // Find the activity by ID
        $activity = Activity::find($activity_id);

        // Return error if activity not found
        if (!$activity) {
            return response()->json([
                "message" => "Activity not found"
            ], Response::HTTP_NOT_FOUND);
        }

        // Mark the activity as reviewed
        $activity->is_reviewed = true;
        try {
            $activity->save();
        } catch (\Throwable $th) {
            // Return error response if saving fails
            return response()->json([
                "message" => "Failed to mark activity as reviewed"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success response
        return response()->json([
            "data" => new ActivityResource($activity),
            "message" => "Activity marked as reviewed"
        ], Response::HTTP_OK);
    }
}
