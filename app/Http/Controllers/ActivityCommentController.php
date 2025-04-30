<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityComment;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ActivityCommentResource;
use App\Http\Resources\CommentsPerActivityResource;

#[OA\Info(
    title: "Activity Comments API",
    version: "1.0.0",
    description: "API for managing activity comments"
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: "ActivityComment",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "activity_id", type: "integer", example: 5),
        new OA\Property(property: "user_id", type: "integer", nullable: true, example: 10),
        new OA\Property(property: "content", type: "string", example: "This is a sample comment"),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time",
            example: "2023-05-15T10:00:00Z"
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time",
            example: "2023-05-15T11:30:00Z"
        )
    ]
)]
class ActivityCommentController extends Controller
{

    private $is_development;
    private $module = 'activity-comments';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanActivityCommentData(array $data): array
    {
        $cleanData = [];

        if (isset($data['comment'])) {
            $cleanData['comment'] = strip_tags($data['comment']);
        }

        return $cleanData;
    }

    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata = ["methods" => ["GET, POST, PUT, DELETE"]];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?activity_comment_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
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
                $metadata['fields'] = ["comment"];
            }

            return $metadata;
        }

        $metadata = ['methods' => ["GET, PUT, DELETE"]];

        if ($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];

            $metadata["fields"] =  ["comment"];
        }

        return $metadata;
    }

    #[OA\Get(
        path: "/api/activity-comments",
        summary: "List all activity comments",
        description: "Returns paginated list of activity comments with optional filtering",
        tags: ["Activity Comments"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Items per page (default: 15, max: 100)",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15, minimum: 1, maximum: 100)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: "activity_id",
                in: "query",
                description: "Filter by activity ID",
                required: false,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/ActivityComment")
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer"),
                                new OA\Property(property: "per_page", type: "integer"),
                                new OA\Property(property: "total", type: "integer")
                            ]
                        ),
                        new OA\Property(
                            property: "links",
                            properties: [
                                new OA\Property(property: "first", type: "string"),
                                new OA\Property(property: "last", type: "string"),
                                new OA\Property(property: "prev", type: "string", nullable: true),
                                new OA\Property(property: "next", type: "string", nullable: true)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthenticated"
            )
        ]
    )]
    public function index()
    {
        // Get all comments with their related user and assigned area information
        $comments = ActivityComment::with(['user.assignedArea'])
            ->get();

        if ($comments->isEmpty()) {
            return response()->json([
                'message' => 'No activity comments found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "data" => ActivityCommentResource::collection($comments),
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "urls" => [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?activity_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ]
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/activity-comments",
        summary: "Create a new activity comment",
        description: "Creates a new comment for an activity",
        tags: ["Activity Comments"],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: "Comment data",
            required: true,
            content: new OA\JsonContent(
                required: ["activity_id", "content"],
                properties: [
                    new OA\Property(property: "activity_id", type: "integer", example: 5),
                    new OA\Property(property: "content", type: "string", example: "This is a new comment"),
                    new OA\Property(property: "user_id", type: "integer", nullable: true, example: 10)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Comment created successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation errors",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            additionalProperties: new OA\Property(
                                type: "array",
                                items: new OA\Items(type: "string")
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthenticated"
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'comment' => 'required|string',
        ]);

        $activity = Activity::findOrFail($validated['activity_id']);

        $activity->comments()->create([
            'comment' => $validated['comment'],
            'user_id' => 1
        ]);

        $comment = $activity->comments()->latest()->first();

        return response()->json([
            "data" => new ActivityCommentResource($comment),
            "message" => 'Comment added successfully'
        ], 201);
    }

    #[OA\Get(
        path: "/api/activity-comments/{id}",
        summary: "Get specific activity comment",
        description: "Returns details of a single activity comment",
        tags: ["Activity Comments"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the comment to retrieve",
                schema: new OA\Schema(type: "integer", example: 1)
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
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthenticated"
            )
        ]
    )]
    public function show($activity_id)
    {
        $activity = Activity::with(['comments.user.assignedArea'])
            ->find($activity_id);

        if (!$activity || !$activity->comments->count()) {
            return response()->json([
                'message' => 'No activity comments found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new CommentsPerActivityResource($activity),
            'message' => 'Comments retrieved successfully'
        ], Response::HTTP_OK);
    }

    #[OA\Put(
        path: "/api/activity-comments/{id}",
        summary: "Update an activity comment",
        description: "Updates the content of an existing activity comment",
        tags: ["Activity Comments"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the comment to update",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Comment data to update",
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Updated comment content")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Comment updated successfully",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation errors"
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthenticated"
            )
        ]
    )]
    public function update(Request $request, ActivityComment $activityComment)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        $activityComment->update($validated);
        return $activityComment;
    }

    #[OA\Delete(
        path: "/api/activity-comments/{id}",
        summary: "Delete an activity comment",
        description: "Permanently removes an activity comment",
        tags: ["Activity Comments"],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the comment to delete",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Comment deleted successfully"
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: "Unauthenticated"
            )
        ]
    )]
    public function destroy(ActivityComment $activityComment)
    {
        $activityComment->delete();
        return response()->noContent();
    }
}
