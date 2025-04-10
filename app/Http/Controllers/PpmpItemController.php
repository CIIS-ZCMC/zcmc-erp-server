<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpItemRequest;
use App\Http\Resources\PpmpItemResource;
use App\Models\AopApplication;
use App\Models\PpmpItem;
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
class PpmpItemController extends Controller
{
    private $is_development;

    private $module = 'ppmp_items';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?ppmp_item_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $metadata;
        }

        if ($method === 'put') {
            $metadata = ["methods" => "[PUT]"];

            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2"
                ];
                $metadata['fields'] = ["type"];
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

            $metadata["fields"] = ["type"];
        }

        return $metadata;
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
    public function index()
    {
        //paginate display 10 data per page
        $ppmp_item = PpmpItem::whereNull('deleted_at')->paginate(10);

        if ($ppmp_item->isEmpty()) {
            return response()->json([
                'message' => "No record found.",
                "metadata" => $this->getMetadata('get')
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => PpmpItemResource::collection($ppmp_item),
            'meta' => [
                'current_page' => $ppmp_item->currentPage(),
                'last_page' => $ppmp_item->lastPage(),
                'per_page' => $ppmp_item->perPage(),
                'total' => $ppmp_item->total()
            ],
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
    public function store(PpmpItemRequest $request)
    {
        $createdItems = [];

        foreach ($request['ppmp_item'] as $item) {
            $total_amount = $item['total_quantity'] * $item['estimated_budget'];

            $ppmpItem = new PpmpItem();
            $ppmpItem->ppmp_application_id = $item['ppmp_application_id'];
            $ppmpItem->item_id = $item['item_id'];
            $ppmpItem->procurement_mode_id = $item['procurement_mode_id'];
            $ppmpItem->item_request_id = $item['item_request_id'];
            $ppmpItem->remarks = $item['remarks'] ?? null;
            $ppmpItem->estimated_budget = $item['estimated_budget'] ?? null;
            $ppmpItem->total_quantity = $item['total_quantity'] ?? null;
            $ppmpItem->total_amount = $total_amount;
            $ppmpItem->save();

            $createdItems[] = $ppmpItem;
        }

        return response()->json([
            'data' => PpmpItemResource::collection($createdItems),
            'message' => "PPMP Item created successfully."
        ], Response::HTTP_CREATED);
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
    public function show(PpmpItem $ppmpItem)
    {
        return response()->json([
            'data' => new PpmpItemResource($ppmpItem),
            'message' => "PPMP Item retrieved successfully."
        ], Response::HTTP_OK);
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
    public function update(PpmpItemRequest $request, PpmpItem $ppmpItem)
    {
        $data = $request->all();
        $ppmpItem->update($data);

        return response()->json([
            'data' => new PpmpItemResource($ppmpItem),
            'message' => "PPMP Item updated successfully."
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
    public function destroy(PpmpItem $ppmpItem)
    {
        // Check if the PPMP Item exists
        if (!$ppmpItem) {
            return response()->json([
                'message' => "PPMP Item not found."
            ], Response::HTTP_NOT_FOUND);
        }

        $ppmpItem->delete();

        return response()->json([
            'message' => "PPMP Item deleted successfully."
        ], Response::HTTP_NO_CONTENT);
    }
}
