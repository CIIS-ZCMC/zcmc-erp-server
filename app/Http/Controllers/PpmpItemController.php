<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpItemRequest;
use App\Http\Resources\PpmpItemResource;
use App\Models\Activity;
use App\Models\AopApplication;
use App\Models\PpmpItem;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        //paginate display 10 data per page
        $ppmp_item = PpmpItem::with([
            'ppmpApplication',
            'item',
            'procurementMode',
            'activities',
            'comments',
            'ppmpSchedule'
        ])->whereNull('deleted_at')
            ->paginate($perPage, ['*'], 'page', $page);


        if ($ppmp_item->isEmpty()) {
            return response()->json([
                'message' => "No record found.",
                'meta' => [
                    'current_page' => $ppmp_item->currentPage(),
                    'last_page' => $ppmp_item->lastPage(),
                    'per_page' => $ppmp_item->perPage(),
                    'total' => $ppmp_item->total(),
                    'from' => $ppmp_item->firstItem(),
                    'to' => $ppmp_item->lastItem()
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => PpmpItemResource::collection($ppmp_item),
            'meta' => [
                'current_page' => $ppmp_item->currentPage(),
                'last_page' => $ppmp_item->lastPage(),
                'per_page' => $ppmp_item->perPage(),
                'total' => $ppmp_item->total(),
                'from' => $ppmp_item->firstItem(),
                'to' => $ppmp_item->lastItem()
            ],
            'links' => [
                'first' => $ppmp_item->url(1),
                'last' => $ppmp_item->url($ppmp_item->lastPage()),
                'prev' => $ppmp_item->previousPageUrl(),
                'next' => $ppmp_item->nextPageUrl()
            ],
            'message' => "PPMP Items retrieved successfully.",
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
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            $createdItems = [];

            $activity = Activity::findOrFail($validatedData['activity_id']);

            foreach ($validatedData['ppmp_item'] as $item) {
                // Calculate total amount
                $totalAmount = ($item['total_quantity'] ?? 0) * ($item['estimated_budget'] ?? 0);

                // Create PPMP item
                $ppmpItem = PpmpItem::create([
                    'ppmp_application_id' => $item['ppmp_application_id'],
                    'item_id' => $item['item_id'],
                    'procurement_mode_id' => $item['procurement_mode_id'],
                    'item_request_id' => $item['item_request_id'],
                    'remarks' => $item['remarks'] ?? null,
                    'estimated_budget' => $item['estimated_budget'] ?? 0,
                    'total_quantity' => $item['total_quantity'] ?? 0,
                    'total_amount' => $totalAmount,
                ]);

                // Associate PPMP item with the activity (pivot)
                $activity->ppmpItems()->attach($ppmpItem->id, [
                    'remarks' => $item['remarks'] ?? null,
                    'is_draft' => $request->is_draft ?? 0,
                ]);

                // Create resource recordp
                $resource = new Resource();
                $resource->activity_id = $activity->id;
                $resource->item_id = $item['item_id'];
                $resource->purchase_type_id = $item['procurement_mode_id'];
                $resource->object_category = $validatedData['object_category'] ?? null;
                $resource->quantity = $item['total_quantity'] ?? null;
                $resource->expense_class = $validatedData['expense_class'] ?? null;
                $resource->save();

                $createdItems[] = $ppmpItem;
            }

            DB::commit();

            return response()->json([
                //'data' => PpmpItemResource::collection($createdItems),
                'message' => 'PPMP Items created successfully.',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating PPMP Items: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
