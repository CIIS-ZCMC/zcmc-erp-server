<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\SuccessIndicatorRequest;
use App\Http\Resources\SuccessIndicatorDuplicateResource;
use App\Http\Resources\SuccessIndicatorResource;
use App\Models\SuccessIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Success Indicator",
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
class SuccessIndicatorController extends Controller
{
    private $is_development;

    private $module = 'success-indicators';
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanSuccessIndicatorData(array $data): array
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
    
    protected function search(Request $request, $start): AnonymousResourceCollection
    {   
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);
        
        $searchTerm = '%'.trim($validated['search']).'%';
        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $results = SuccessIndicator::where('code', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return SuccessIndicatorResource::collection($results)
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
    
    protected function all($start): AnonymousResourceCollection
    {
        $objective_success_indicator = SuccessIndicator::all();

        return SuccessIndicatorResource::collection($objective_success_indicator)
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
        
        $objective_success_indicator = SuccessIndicator::paginate($perPage, ['*'], 'page', $page);

        return SuccessIndicatorResource::collection($objective_success_indicator)
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
    protected function singleRecord($success_indicator_id, $start):JsonResponse
    {
        $successIndicator = SuccessIndicator::find($success_indicator_id);
            
        if (!$successIndicator) {
            return response()->json(["message" => "Success indicator not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new SuccessIndicatorResource($successIndicator))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start):SuccessIndicatorResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = SuccessIndicator::whereIn('description', collect($request->success_indicators)->pluck('description'))
            ->orWhereIn('code', collect($request->success_indicators)->pluck('code'))
            ->get(['description', 'code'])->toArray();

        // Convert existing items into a searchable format
        $existing_codes = array_column($existing_items, 'code');
        $existing_descriptions = array_column($existing_items, 'description');

        foreach ($request->success_indicators as $item) {
            if (!in_array($item['code'], $existing_codes) && !in_array($item['description'], $existing_descriptions)) {
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
                'data' => $existing_items,
                'message' => "Failed to bulk insert all success indicators already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        SuccessIndicator::insert($cleanData);

        $latest_item_units = SuccessIndicator::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return SuccessIndicatorResource::collection($latest_item_units)
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "existings" => $existing_items,
                ],
                "message" => "Successfully store data."
            ]);
    }
    
    protected function bulkUpdate(Request $request, $start): AnonymousResourceCollection|JsonResponse
    {
        $success_indicator_ids = $request->query('id') ?? null;

        if (count($success_indicator_ids) !== count($request->input('success_indicators'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of success indicators provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_success_indicators = [];
        $errors = [];
    
        foreach ($success_indicator_ids as $index => $id) {
            $success_indicator = SuccessIndicator::find($id);
            
            if (!$success_indicator) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanSuccessIndicatorData($request->input('success_indicators')[$index]);
            $success_indicator->update($cleanData);
            $updated_success_indicators[] = $success_indicator;
        }
    
        if (!empty($errors)) {
            return SuccessIndicatorResource::collection($updated_success_indicators)
                ->additional([
                    "meta" => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                        'issue' => $errors,
                        "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                    ],
                    "message" => "Partial update completed with errors.",
                ])
                ->response()
                ->setStatusCode(Response::HTTP_MULTI_STATUS);
        }
        
        return SuccessIndicatorResource::collection($updated_success_indicators)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|SuccessIndicatorResource|JsonResponse
    {
        $success_indicator_ids = $request->query('id') ?? null;
        
        // Convert single ID to array for consistent processing
        $success_indicator_ids = is_array($success_indicator_ids) ? $success_indicator_ids : [$success_indicator_ids];
    
        // Handle bulk update
        if ($request->has('success_indicators')) {
            $this->bulkUpdate($request, $start);
        }
    
        // Handle single update
        $success_indicator = SuccessIndicator::find($success_indicator_ids[0]);
        
        if (!$success_indicator) {
            return response()->json([
                "message" => "Success indicator not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanSuccessIndicatorData($request->all());
        $success_indicator->update($cleanData);

        return (new SuccessIndicatorResource($success_indicator))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update success indicator record.'
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
                            additionalProperties: new OA\Property(type: 'array', items: new OA\Items(type: 'string')))
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
        path: "/api/success-indicators",
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
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);
        $success_indicator_id = $request->query('id');
        $search = $request->search;
        $mode = $request->mode;

        if($success_indicator_id){
            return $this->singleRecord($success_indicator_id, $start);
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
        path: "/api/success-indicators",
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
    public function store(SuccessIndicatorRequest $request): AnonymousResourceCollection|JsonResponse|SuccessIndicatorResource
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->success_indicators !== null || $request->success_indicators > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = SuccessIndicator::create($cleanData);
        
        return (new SuccessIndicatorResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ])->response();
    }

    #[OA\Put(
        path: "/api/success-indicators/{id}",
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
        $success_indicator_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$success_indicator_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->methods, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->success_indicators !== null || $request->success_indicators > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    #[OA\Delete(
        path: "/api/success-indicators/{id}",
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
        $success_indicator_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$success_indicator_ids && !$query) {
            $response = ["message" => "Invalid request. No parameters provided."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found for deletion.",
                    "meta" => MetadataComposerHelper::compose('delete', $this->methods, $this->is_development),
                    "hint" => "Provide either 'id' or 'query' parameter"
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($success_indicator_ids) {
            // Handle all ID formats: single, comma-separated, and array-style
            $success_indicator_ids = is_array($success_indicator_ids) 
                ? $success_indicator_ids 
                : (str_contains($success_indicator_ids, ',') 
                    ? explode(',', $success_indicator_ids) 
                    : [$success_indicator_ids]);

            // Validate and sanitize IDs
            $valid_ids = array_filter(array_map(function($id) {
                return is_numeric($id) && $id > 0 ? (int)$id : null;
            }, $success_indicator_ids));

            if (empty($valid_ids)) {
                return response()->json(
                    ["message" => "Invalid success indicator ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Get only active success indicators that exist
            $success_indicators = SuccessIndicator::whereIn('id', $valid_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($success_indicators->isEmpty()) {
                return response()->json(
                    ["message" => "No active success indicators found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Perform soft delete
            $deleted_count = SuccessIndicator::whereIn('id', $valid_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted {$deleted_count} success indicator(s).",
                "deleted_ids" => $valid_ids,
                "count" => $deleted_count
            ], Response::HTTP_OK);
        }

        $success_indicators = SuccessIndicator::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($success_indicators->count() > 1) {
            return response()->json([
                'data' => $success_indicators,
                'message' => "Query matches multiple success indicators.",
                'suggestion' => "Use ID parameter for precise deletion or add more query criteria"
            ], Response::HTTP_CONFLICT);
        }

        $success_indicator = $success_indicators->first();

        if (!$success_indicator) {
            return response()->json(
                ["message" => "No active success indicator found matching your criteria."],
                Response::HTTP_NOT_FOUND
            );
        }

        $success_indicator->delete();

        return response()->json([
            "message" => "Successfully deleted success indicator.",
            "deleted_id" => $success_indicator->id,
            "indicator_name" => $success_indicator->name
        ], Response::HTTP_OK);
    }
}
