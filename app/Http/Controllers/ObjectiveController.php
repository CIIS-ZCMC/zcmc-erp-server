<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Requests\ObjectiveRequest;
use App\Http\Resources\ObjectiveDuplicateResource;
use App\Http\Resources\ObjectiveResource;
use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use App\Models\ObjectiveSuccessIndicator;

#[OA\Schema(
    schema: "Objective",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "code", type: "string", nullable: true),
        new OA\Property(property: "description", type: "string"),
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

    private $methods = '[GET, POST, PUT, DELETE]';

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
    
    // Protected function
    protected function search(Request $request, $start): JsonResource
    {   
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);
        
        $searchTerm = '%'.trim($validated['search']).'%';
        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $results = Objective::where('code', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ObjectiveResource::collection($results)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'search' => [
                        'term' => $validated['search'],
                        'time_ms' => round((microtime(true) - $start) * 1000), // in milliseconds
                    ],
                    'pagination' => [
                        'total' => $results->total(),
                        'per_page' => $results->perPage(),
                        'current_page' => $results->currentPage(),
                        'last_page' => $results->lastPage(),
                    ]
                ],
                'message' => 'Search completed successfully'
            ]);
    }
    
    protected function all($start)
    {
        $objective_success_indicator = Objective::all();

        return ObjectiveResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }
    
    protected function pagination(Request $request, $start): AnonymousResourceCollection
    {   
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;
        
        $objective_success_indicator = Objective::paginate($perPage, ['*'], 'page', $page);

        return ObjectiveResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'pagination' => [
                        'total' => $objective_success_indicator->total(),
                        'per_page' => $objective_success_indicator->perPage(),
                        'current_page' => $objective_success_indicator->currentPage(),
                        'last_page' => $objective_success_indicator->lastPage(),
                    ]
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }     

    protected function singleRecord($item_unit_id, $start):JsonResponse
    {
        $itemUnit = Objective::find($item_unit_id);
            
        if (!$itemUnit) {
            return response()->json(["message" => "Item unit not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ObjectiveResource($itemUnit))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start):ObjectiveResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = Objective::whereIn('code', collect($request->objectives)->pluck('code'))
        ->orWhereIn('description', collect($request->objectives)->pluck('description'))
        ->get(['code', 'description'])->toArray();

        // Convert existing items into a searchable format
        $existing_names = array_column($existing_items, 'code');
        $existing_codes = array_column($existing_items, 'description');

        foreach ($request->objectives as $objective) {
            if (!in_array($objective['code'], $existing_names) && !in_array($objective['description'], $existing_codes)) {
                $cleanData[] = [
                    "code" => strip_tags($objective['code']),
                    "description" => isset($objective['description']) ? strip_tags($objective['description']) : null,
                    "created_at" => now(),
                    "updated_at" => now()
                ];
            }
        }

        if (empty($cleanData) && count($existing_items) > 0) {
            return response()->json([
                'data' => $existing_items,
                'message' => "Failed to bulk insert all objectives already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Objective::insert($cleanData);

        $latest_objective = Objective::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ObjectiveResource::collection($latest_objective)
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "existings" => $existing_items,
                ],
                "message" => "Successfully store data."
            ]);
    }
    
    protected function bulkUpdate(Request $request, $start):AnonymousResourceCollection|JsonResponse
    {
        $objective_ids = $request->query('id') ?? null;

        if (count($objective_ids) !== count($request->input('objectives'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of objectives provided.",
                // "meta" => MetadataComposerHelper::compose('put', $this->module)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_objectives = [];
        $errors = [];
    
        foreach ($objective_ids as $index => $id) {
            $objective = Objective::find($id);
            
            if (!$objective) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanObjectivesData($request->input('objectives')[$index]);
            $objective->update($cleanData);
            $updated_objectives[] = $objective;
        }
    
        if (!empty($errors)) {
            return ObjectiveResource::collection($updated_objectives)
                ->additional([
                    "meta" => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                        'issue' => $errors,
                    ],
                    "message" => "Partial update completed with errors.",
                ])
                ->response()
                ->setStatusCode(Response::HTTP_MULTI_STATUS);
        }
        
        return ObjectiveResource::collection($updated_objectives)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    // "url_format" => MetadataComposerHelper::compose('put', $this->module)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|ObjectiveResource|JsonResponse
    {
        $objectives = $request->query('id') ?? null;
        
        // Convert single ID to array for consistent processing
        $objectives = is_array($objectives) ? $objectives : [$objectives];
    
        // Handle bulk update
        if ($request->has('objectives')) {
            $this->bulkUpdate($request, $start);
        }
    
        // Handle single update
        $objective = Objective::find($objectives[0]);
        
        if (!$objective) {
            return response()->json([
                "message" => "Objective not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanObjectivesData($request->all());
        $objective->update($cleanData);

        return (new ObjectiveResource($objective))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update objective record.'
            ])->response();
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
        tags: ['Objectives']
    )]
    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
    }

    #[OA\Get(
        path: "/api/objectives",
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
    public function index(GetWithPaginatedSearchModeRequest $request): AnonymousResourceCollection|JsonResource|JsonResponse
    {
        $start = microtime(true);
        $item_unit_id = $request->query('id');
        $search = $request->search;
        $mode = $request->mode;

        if($item_unit_id){
            return $this->singleRecord($item_unit_id, $start);
        }

        if($mode && $mode === 'selection'){
            return $this->all($start);
        }
        
        if($search){
            return $this->search($request, $start);
        }

        return $this->pagination($request, $start);
    }

    #[OA\Post(
        path: "/api/objectives",
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
    public function store(ObjectiveRequest $request): AnonymousResourceCollection|JsonResponse|ObjectiveResource
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->objectives !== null || $request->objectives > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = Objective::create($cleanData);
        
        return (new ObjectiveResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ])->response();
    }

    #[OA\Put(
        path: "/api/objectives/{id}",
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
    public function update(Request $request): AnonymousResourceCollection|JsonResource|JsonResponse    
    {
        $start = microtime(true);
        $objectives = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$objectives) {
            $response = ["message" => "ID parameter is required."];

            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->module, $this->is_development);
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->objectives !== null || $request->objectives > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    #[OA\Delete(
        path: "/api/objectives/{id}",
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
                    $response['meta'] = MetadataComposerHelper::compose('delete', $this->module, $this->is_development)
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
            
            $deletedCount = Objective::whereIn('id', $found_ids)->delete();
    
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

        $objective->delete();

        return response()->json([
            "message" => "Successfully deleted objective.",
            "deleted_id" => $objective->id,
            "objective_name" => $objective->name // Include relevant objective info
        ], Response::HTTP_OK);
    }
}
