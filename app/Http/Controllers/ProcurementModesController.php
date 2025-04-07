<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcurementModeRequest;
use App\Http\Resources\ProcurementModeResource;
use App\Models\ProcurementModes;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

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
class ProcurementModesController extends Controller
{
    private $module = 'procurement-modes';

    private function getMetaData()
    {
        return ["methods" => ['GET, POST, PUT, DELETE']];
    }

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
    public function index(Request $request)
    {
        $procurementModes = ProcurementModes::where('deleted_at', NULL)->all();

        return response()->json([
            "data" => ProcurementModeResource::collection($procurementModes),
            "metadata" => $this->getMetaData(),
        ], Response::HTTP_OK);
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
    public function store(ProcurementModeRequest $request)
    {
        $name = $request->input("name");

        if(!$name){
            return response()->json([
                "message" => "Field name is required."
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cleanData = ['name' => strip_tags($name)];

        $has_record = ProcurementModes::where('name', $name)->get();

        if(count($has_record) > 0){
            return response()->json(['message' => "Procurement mode already exist."], Response::HTTP_BAD_REQUEST);
        }

        $procurement_mode = ProcurementModes::create($cleanData);

        return response()->json([
            "data" => $procurement_mode,
            "metadata" => $this->getMetaData()
        ], Response::HTTP_CREATED);
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
    public function update(Request $request, ProcurementModes $procurementMode)
    {
        $name = strip_tags($request->input("name"));

        $procurementMode->update($request->all());

        return response()->json([
            'data' => $procurementMode,
            "metadata" => $this->getMetaData()
        ], Response::HTTP_OK);
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
    public function destroy(ProcurementModes $procurementMode)
    {
        $procurementMode->update(['deleted_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
