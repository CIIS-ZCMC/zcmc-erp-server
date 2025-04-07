<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ObjectiveRequest;
use App\Http\Resources\ObjectiveDuplicateResource;
use App\Http\Resources\ObjectiveResource;
use App\Models\Objective;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use App\Models\ObjectiveSuccessIndicator;

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
class ObjectiveController extends Controller
{
    private $is_development;

    private $module = 'objectives';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanObjectivesData(array $data): array
    {
        $cleanData = [];

        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }

        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }

        return $cleanData;
    }

    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?objective_id=[primary-key]",
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
                $metadata['fields'] = ["title", "code", "description"];
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

            $metadata["fields"] =  ["code"];
        }

        return $metadata;
    }

    #[OA\Post(
        path: '/api/import',
        summary: 'Import item units from Excel/CSV file',
        requestBody: new OA\RequestBody(
            description: 'Excel/CSV file containing item units',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary',
                            description: 'Excel file (xlsx, xls, csv)'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful import',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'success_count', type: 'integer'),
                        new OA\Property(property: 'failure_count', type: 'integer'),
                        new OA\Property(
                            property: 'failures',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'row', type: 'integer'),
                                    new OA\Property(property: 'errors', type: 'array', items: new OA\Items(type: 'string'))
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            additionalProperties: new OA\Property(type: 'array', items: new OA\Items(type: 'string'))
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'error', type: 'string')
                    ]
                )
            )
        ],
        tags: ['Item Units']
    )]
    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
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
        $page = $request->query('page') > 0 ? $request->query('page') : 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $objective_id = $request->query('objective_id') ?? null;

        if ($objective_id) {
            $purchase_type = Objective::find($objective_id);

            if (!$purchase_type) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => $purchase_type,
                "metadata" => $this->getMetadata('get')
            ], Response::HTTP_OK);
        }

        if ($page < 0 || $per_page < 0) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "Invalid value of parameters",
                    "metadata" => $this->getMetadata('get')
                ];
            }

            return response()->json([$response], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$page && !$per_page) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('get')
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle return for selection record
        if ($mode === 'selection') {
            if ($search !== null) {
                $objectives = Objective::select('id', 'code', 'description')
                    ->where('code', 'like', "%" . $search . "%")
                    ->where("deleted_at", NULL)->get();

                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

                if ($this->is_development) {
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }

                return response()->json([
                    "data" => $objectives,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $objectives = Objective::select('id', 'code', 'description')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if ($this->is_development) {
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }

            return response()->json([
                "data" => $objectives,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }


        if ($search !== null) {
            if ($last_id === 0 || $page_item != null) {
                $objectives = Objective::where('code', 'like', '%' . $search . '%')
                    ->where('id', '>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if (count($objectives)  === 0) {
                    return response()->json([
                        'data' => [],
                        'metadata' => [
                            'methods' => '[GET,POST,PUT,DELETE]',
                            'pagination' => [],
                            'page' => 0,
                            'total_page' => 0
                        ],
                    ], Response::HTTP_OK);
                }

                $allIds = Objective::where('code', 'like', '%' . $search . '%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);

                $pagination_helper = new PaginationHelper('objectives', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination($page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);

                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $objectives,
                    'metadata' => [
                        'methods' => '[GET,POST,PUT,DELETE]',
                        'pagination' => $pagination,
                        'page' => $page,
                        'total_page' => count($chunks)
                    ],
                ], Response::HTTP_OK);
            }

            /**
             * Reuse existing pagination and update the existing pagination next and previous data
             */

            $objectives = Objective::where('code', 'like', '%' . $search . '%')
                ->where('id', '>', $last_id)
                ->orderBy('id')->limit($per_page)->get();

            // Return the response
            return response()->json([
                'data' => $objectives,
                'metadata' => []
            ], Response::HTTP_OK);
        }

        $total_page = Objective::all()->pluck('id')->chunk($per_page);
        $objectives = Objective::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));

        $pagination_helper = new PaginationHelper($this->module, $page, $per_page, $total_page > 10 ? 10 : $total_page);

        return response()->json([
            "data" => $objectives,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
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
    public function store(ObjectiveRequest $request)
    {
        $base_message = "Successfully created objectives";

        // Bulk Insert
        if ($request->objectives !== null || $request->objectives > 1) {
            $existing_objectives = [];
            $existing_items = Objective::whereIn('code', collect($request->objectives)->pluck('code'))
                ->get(['code'])->toArray();

            // Convert existing items into a searchable format
            $existing_codes = array_column($existing_items, 'code');

            if (!empty($existing_items)) {
                $existing_objectives = ObjectiveDuplicateResource::collection(Objective::whereIn("code", $existing_codes)->get());
            }

            foreach ($request->objectives as $item) {
                if (!in_array($item['code'], $existing_codes)) {
                    $cleanData[] = [
                        "code" => strip_tags($item['code']),
                        "description" => isset($item['description']) ? strip_tags($item['description']) : null,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_objectives,
                    'message' => "Failed to bulk insert all objectives already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Objective::insert($cleanData);

            $latest_objectives = Objective::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_objectives) > 1 ? $base_message . "s record" : $base_message . " record.";

            return response()->json([
                "data" => $latest_objectives,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_objectives
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = Objective::create($cleanData);

        return response()->json([
            "data" => $new_item,
            "message" => $base_message . " record.",
            "metadata" => [
                "methods" => ['GET, POST, PUT, DELET'],
            ]
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
    public function update(Request $request): Response
    {
        $objectives = $request->query('id') ?? null;

        // Validate request has IDs
        if (!$objectives) {
            $response = ["message" => "ID parameter is required."];

            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $objectives = is_array($objectives) ? $objectives : [$objectives];

        // For bulk update - validate items array matches IDs count
        if ($request->has('items')) {
            if (count($objectives) !== count($request->input('items'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of objectives provided.",
                    "metadata" => $this->getMetadata("put"),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $updated_items = [];
            $errors = [];

            foreach ($objectives as $index => $id) {
                $item = Objective::find($id);

                if (!$item) {
                    $errors[] = "Objectives with ID {$id} not found.";
                    continue;
                }

                $itemData = $request->input('items')[$index];
                $cleanData = $this->cleanObjectivesData($itemData);

                $item->update($cleanData);
                $updated_items[] = $item;
            }

            if (!empty($errors)) {
                return response()->json([
                    "data" => ObjectiveResource::collection($updated_items),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }

            return response()->json([
                "data" => ObjectiveResource::collection($updated_items),
                "message" => "Successfully updated " . count($updated_items) . " items.",
                "metadata" => $this->getMetadata('put')
            ], Response::HTTP_OK);
        }

        // Single item update
        if (count($objectives) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no items array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Objective::find($objectives[0]);

        if (!$item) {
            return response()->json([
                "message" => "Objectives not found."
            ], Response::HTTP_NOT_FOUND);
        }

        $cleanData = $this->cleanObjectivesData($request->all());
        $item->update($cleanData);

        $response = [
            "data" => new ObjectiveResource($item),
            "message" => "Objective updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function updateForApproverModule($id)
    {
        $objectiveSuccessIndicator = ObjectiveSuccessIndicator::with(
            'objective',
            'successIndicator'
        )->findOrFail($id);

        try {
            DB::beginTransaction();

            // Update objective if provided
            if (request()->has('objective')) {
                $cleanData = $this->cleanObjectivesData(request()->input('objective'));
                $objectiveSuccessIndicator->objective->update($cleanData);
            }

            // Update success indicator if provided
            if (request()->has('success_indicator')) {
                $successIndicatorId = request()->input('success_indicator')['success_indicator_id'] ?? null;
                
                if ($successIndicatorId) {
                    $objectiveSuccessIndicator->update([
                        'success_indicator_id' => $successIndicatorId,
                    ]);
                }
            }

            // Create new objective success indicator if requested
            if (request()->has('new_success_indicator') && !request()->has('objective_success_indicator_id')) {
                $newSuccessIndicatorId = request()->input('new_success_indicator')['success_indicator_id'] ?? null;
                
                if ($newSuccessIndicatorId) {
                    $objectiveSuccessIndicator->objective->objectiveSuccessIndicators()->create([
                        'success_indicator_id' => $newSuccessIndicatorId,
                    ]);
                }
            }

            DB::commit();
            
            return response()->json([
                'data' => $objectiveSuccessIndicator->objective->fresh(['objectiveSuccessIndicators', 'objectiveSuccessIndicators.successIndicator']),
                'message' => 'Objective and success indicator updated successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update objective and success indicator',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
    public function destroy(Request $request): Response
    {
        $objective_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$objective_ids && !$query) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('delete')
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($objective_ids) {
            $objective_ids = is_array($objective_ids)
                ? $objective_ids
                : (str_contains($objective_ids, ',')
                    ? explode(',', $objective_ids)
                    : [$objective_ids]
                );

            $objective_ids = array_filter(array_map('intval', $objective_ids));

            if (empty($objective_ids)) {
                return response()->json(
                    ["message" => "Invalid objective ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $objectives = Objective::whereIn('id', $objective_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($objectives->isEmpty()) {
                return response()->json(
                    ["message" => "No active objectives found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }

            $found_ids = $objectives->pluck('id')->toArray();

            $deletedCount = Objective::whereIn('id', $found_ids)
                ->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted {$deletedCount} objective(s).",
                "deleted_ids" => $found_ids,
                "count" => $deletedCount
            ], Response::HTTP_OK);
        }

        $objectives = Objective::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($objectives->count() > 1) {
            return response()->json([
                'data' => $objectives,
                'message' => "Query matches multiple objectives. Please specify IDs directly.",
                'suggestion' => "Use ?id parameter for bulk operations or add more specific query criteria."
            ], Response::HTTP_CONFLICT);
        }

        $objective = $objectives->first();

        if (!$objective) {
            return response()->json(
                ["message" => "No active objective found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $objective->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted objective.",
            "deleted_id" => $objective->id,
            "objective_name" => $objective->name // Include relevant objective info
        ], Response::HTTP_OK);
    }
}
