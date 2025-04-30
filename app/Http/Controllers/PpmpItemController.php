<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpItemRequest;
use App\Http\Requests\PpmpScheduleRequest;
use App\Http\Requests\ResourceRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Http\Resources\PpmpItemResource;
use App\Models\Activity;
use App\Models\Item;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\ProcurementModes;
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

        $ppmp_application = PpmpApplication::with([
            'user',
            'divisionChief',
            'budgetOfficer',
            'aopApplication',
        ])
            ->whereNull('deleted_at')
            ->first();

        if (!$ppmp_application) {
            return response()->json([
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        }

        // Paginate ppmp_items inside the application
        $ppmp_application->ppmp_items_paginated = $ppmp_application->ppmpItems()
            ->whereNull('ppmp_items.deleted_at')
            ->with([
                'item' => function ($query) {
                    $query->with([
                        'itemUnit',
                        'itemCategory',
                        'itemClassification',
                        'itemSpecifications',
                    ]);
                },
                'procurementMode',
                'itemRequest',
                'activities',
                'comments',
                'ppmpSchedule',
            ])
            ->paginate($perPage, ['*'], 'page', $page);


        return response()->json([
            'data' => new PpmpApplicationResource($ppmp_application),
            'message' => 'PPMP Application retrieved successfully.',
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

            $ppmp_application = PpmpApplication::latest()->first();

            if (!$ppmp_application) {
                return response()->json([
                    'message' => "No record found.",
                ], Response::HTTP_NOT_FOUND);
            }

            $monthMap = [
                'jan' => 1,
                'feb' => 2,
                'mar' => 3,
                'apr' => 4,
                'may' => 5,
                'jun' => 6,
                'jul' => 7,
                'aug' => 8,
                'sep' => 9,
                'oct' => 10,
                'nov' => 11,
                'dec' => 12,
            ];

            foreach ($validatedData['PPMP_Items'] as $item) {

                $procurement_mode = ProcurementModes::where('name', $item['procurement_mode'])->first();
                $items = Item::where('code', $item['item_code'])->first();

                // Create PPMP item
                $ppmpItem = PpmpItem::create([
                    'ppmp_application_id' => $ppmp_application->id ?? 1,
                    'item_id' => $items->id,
                    'procurement_mode_id' => $procurement_mode->id,
                    'item_request_id' => $item['item_request_id'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                    'estimated_budget' => $item['estimated_budget'] ?? 0,
                    'total_quantity' => $item['quantity'] ?? 0,
                    'total_amount' => $item['total_amount'] ?? 0,
                ]);

                foreach ($item['activities'] as $activity) {
                    $activities = Activity::find($activity['activity_id'] ?? $activity['id'] ?? null);

                    if ($activities) {
                        $activities->ppmpItems()->attach($ppmpItem->id, [
                            'remarks' => $item['remarks'] ?? null,
                            'is_draft' => $request->is_draft ?? 0,
                        ]);

                        $resource_request = [
                            'activity_id' => $activities->id,
                            'item_id' => $items->id,
                            'purchase_type_id' => $item['purchase_type_id'] ?? 1,
                            'object_category' => $item['category'] ?? null,
                            'quantity' => $item['quantity'] ?? null,
                            'expense_class' => $item['expense_class_id'] ?? null,
                        ];

                        $resource_controller = new ResourceController();
                        $resource_controller->store(new ResourceRequest($resource_request));
                    }
                }

                foreach ($item['target_by_quarter'] as $monthly => $quantity) {
                    if ($quantity >= 0 && isset($monthMap[$monthly])) {
                        $target_request = [
                            'ppmp_item_id' => $ppmpItem->id,
                            'month' => $monthMap[$monthly],
                            'year' => now()->addYear()->year,
                            'quantity' => $quantity,
                        ];

                        $ppmp_schedule = new PpmpScheduleController();
                        $ppmp_schedule->store(new PpmpScheduleRequest($target_request));
                    }

                }

                // $createdItems[] = $ppmpItem;
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
