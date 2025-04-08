<?php

namespace App\Http\Controllers;

use App\Models\FunctionObjective;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use App\Models\Objective;
use App\Http\Resources\FunctionObjectiveResource;
use App\Http\Resources\TypeOfFunctionResource;
use App\Models\SuccessIndicator;
use App\Models\ObjectiveSuccessIndicator;

#[OA\Schema(
    schema: "FunctionObjective",
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
class FunctionObjectiveController extends Controller
{
    private $module = 'function-objectives';

    private $methods = '[GET, POST, PUT, DELETE]';

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

    private function generateObjectiveCode(): string
    {
        // Generate a unique objective code
        $prefix = 'OBJ-';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return $prefix . $timestamp . '-' . $random;
    }

    private function generateSuccessIndicatorCode(): string
    {
        // Generate a unique success indicator code
        $prefix = 'SI-';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return $prefix . $timestamp . '-' . $random;
    }

    public function store(Request $request)
    {
        $start = microtime(true);

        $validated = $request->validate([
            'type_of_function_id' => 'required|integer',
            'objective' => 'required|string',
            'success_indicators' => 'required|array',
            'success_indicators.*' => 'required|string'
        ]);

        // Create objective
        $objective = Objective::create([
            'code' => $this->generateObjectiveCode(),
            'description' => $validated['objective']
        ]);

        if (!$objective) {
            return response()->json(['message' => 'Failed to create objective'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Update function objective
        $functionObjective = FunctionObjective::find($validated['type_of_function_id']);
        if (!$functionObjective) {
            return response()->json(['message' => 'Function objective not found'], Response::HTTP_NOT_FOUND);
        }

        $functionObjective->update([
            'type_of_function_id' => $validated['type_of_function_id'],
            'objective_id' => $objective->id
        ]);

        // Create success indicator
        foreach ($validated['success_indicators'] as $successIndicator) {
            $successIndicator = SuccessIndicator::create([
                'code' => $this->generateSuccessIndicatorCode(),
                'description' => $successIndicator
            ]);
        }

        // Create Objective Success Indicator
        foreach ($validated['success_indicators'] as $successIndicator) {
            $objectiveSuccessIndicator = ObjectiveSuccessIndicator::create([
                'objective_id' => $objective->id,
                'success_indicator_id' => $successIndicator->id
            ]);

            if (!$objectiveSuccessIndicator) {
                return response()->json(['message' => 'Failed to create objective success indicator'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return (new FunctionObjectiveResource($functionObjective))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    "time_ms" => round((microtime(true) - $start) * 1000),
                    "type_of_function" => new TypeOfFunctionResource($functionObjective->typeOfFunction)
                ],
                "message" => "Successfully created record."
            ]);
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
    public function show(FunctionObjective $functionObjective)
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
    public function update(Request $request, FunctionObjective $functionObjective)
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
    public function destroy(FunctionObjective $functionObjective)
    {
        //
    }
}
