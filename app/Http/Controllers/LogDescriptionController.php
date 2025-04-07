<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Requests\LogDescriptionRequest;
use App\Http\Resources\LogDescriptionResource;
use App\Imports\LogDescriptionsImport;
use App\Models\LogDescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Log Description",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "title", type: "string"),
        new OA\Property(property: "code", type: "string", nullable: true),
        new OA\Property(property: "description", type: "string", nullable: true),
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
class LogDescriptionController extends Controller
{
    private $is_development;

    private $module = 'log-descriptions';
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    protected function cleanData(array $data): array
    {
        $cleanData = [];
        
        if (isset($data['title'])) {
            $cleanData['title'] = strip_tags($data['title']);
        }
        
        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }

        return $cleanData;
    }
    
    protected function cleanLogDescriptionData(array $data): array
    {
        $cleanData = [];

        if (isset($data['title'])) {
            $cleanData['title'] = strip_tags($data['title']);
        }

        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }

        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }

        return $cleanData;
    }

    protected function all($start)
    {
        $objective_success_indicator = LogDescription::all();

        return LogDescriptionResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }
    
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

        $results = LogDescription::where('title', 'like', "%{$searchTerm}%")
            ->orWhere('code', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return LogDescriptionResource::collection($results)
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
    
    protected function pagination(Request $request, $start)
    {   
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;
        
        $objective_success_indicator = LogDescription::paginate($perPage, ['*'], 'page', $page);

        return LogDescriptionResource::collection($objective_success_indicator)
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
    
    protected function singleRecord($log_description_id, $start):LogDescriptionResource|Response
    {
        $logDescription = LogDescription::find($log_description_id);
            
        if (!$logDescription) {
            return response()->json(["message" => "Log description not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new LogDescriptionResource($logDescription))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }
    
    protected function bulkUpdate(Request $request, $start):AnonymousResourceCollection|JsonResponse
    {
        $log_description_ids = $request->query('id') ?? null;

        if (count($log_description_ids) !== count($request->input('log_descriptions'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of log descriptions provided."
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_log_descriptions = [];
        $errors = [];
    
        foreach ($log_description_ids as $index => $id) {
            $log_description = LogDescription::find($id);
            
            if (!$log_description) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanData($request->input('log_descriptions')[$index]);
            $log_description->update($cleanData);
            $updated_log_descriptions[] = $log_description;
        }
    
        if (!empty($errors)) {
            return LogDescriptionResource::collection($updated_log_descriptions)
                ->additional([
                    "meta" => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                        'issue' => $errors
                    ],
                    "message" => "Partial update completed with errors.",
                ])
                ->response()
                ->setStatusCode(Response::HTTP_MULTI_STATUS);
        }
        
        return LogDescriptionResource::collection($updated_log_descriptions)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully updated log descriptions",
            ]);
    }
    
    protected function singleRecordUpdate(Request $request, $start): JsonResource|LogDescriptionResource|JsonResponse
    {
        $log_description_ids = $request->query('id') ?? null;
        
        // Convert single ID to array for consistent processing
        $log_description_ids = is_array($log_description_ids) ? $log_description_ids : [$log_description_ids];
    
        // Handle bulk update
        if ($request->has('log_descriptions')) {
            $this->bulkUpdate($request, $start);
        }
    
        // Handle single update
        $log_description = LogDescription::find($log_description_ids[0]);
        
        if (!$log_description) {
            return response()->json([
                "message" => "Log description not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanData($request->all());
        $log_description->update($cleanData);

        return (new LogDescriptionResource($log_description))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update log description record.'
            ])->response();
    }

    // Public
    #[OA\Get(
        path: '/api/log-descriptions/template',
        summary: 'Download CSV template for log descriptions',
        description: 'Returns a CSV template file with example log description entries',
        tags: ['Log Descriptions'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'CSV template file download',
                content: new OA\MediaType(
                    mediaType: 'text/csv',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                ),
                headers: [
                    new OA\Header(
                        header: 'Content-Disposition',
                        description: 'Attachment with filename',
                        schema: new OA\Schema(type: 'string'))
                ]
            ),
            new OA\Response(
                response: Response::HTTP_INTERNAL_SERVER_ERROR,
                description: 'Server error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ],
                    example: ['message' => 'Could not generate template file']
                )
            )
        ]
    )]
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="log_descriptions_template.csv"',
        ];
        
        $columns = ['title', 'code', 'description'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, $columns);
            
            fputcsv($file, [
                'Item Created',
                'ITEM-CREATE',
                'Log when a new item is created'
            ]);
            
            fputcsv($file, [
                'User Updated',
                'USER-UPDATE',
                'Log when user details are updated'
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new LogDescriptionsImport;
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getRowCount() - count($import->failures());
            $failures = $import->failures();
            
            return response()->json([
                'message' => "$successCount log descriptions imported successfully.",
                'success_count' => $successCount,
                'failure_count' => count($failures),
                'failures' => $failures->map(function($failure) {
                    return [
                        'row' => $failure->row(),
                        'errors' => $failure->errors(),
                        'values' => $failure->values()
                    ];
                })
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error importing file',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function index(GetWithPaginatedSearchModeRequest $request):AnonymousResourceCollection|JsonResponse|LogDescriptionResource
    {
        $start = microtime(true);
        $log_description_id = $request->query('id');
        $search = $request->search;
        $mode = $request->mode;

        if($log_description_id){
            return $this->singleRecord($log_description_id, $start);
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
    public function store(LogDescriptionRequest $request)
    {
        $start = microtime(true);
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->log_descriptions !== null || $request->log_descriptions > 1) {
            $existing_items = LogDescription::whereIn('title', collect($request->log_descriptions)->pluck('title'))
                ->orWhereIn('code', collect($request->log_descriptions)->pluck('code'))
                ->get(['title', 'code'])->toArray();

            // Convert existing items into a searchable format
            $existing_titles = array_column($existing_items, 'title');
            $existing_codes = array_column($existing_items, 'code');

            foreach ($request->log_descriptions as $item) {
                if (!in_array($item['title'], $existing_titles) && !in_array($item['code'], $existing_codes)) {
                    $cleanData[] = [
                        "title" => strip_tags($item['title']),
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
                    'message' => "Failed to bulk insert all item categories already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            LogDescription::insert($cleanData);

            $latest_item_log_descriptions = LogDescription::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();
            
            return LogDescriptionResource::collection($latest_item_log_descriptions)
                ->additional([
                    'meta' => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                        "duplicate_items" => $existing_items
                    ],
                    'message' => 'Successfully stores log descriptions.'
                ]);
        }

        $cleanData = [
            "title" => strip_tags($request->input('title')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = LogDescription::create([
            "title" => strip_tags($request->title),
            "code" => strip_tags($request->code),
            "description" => strip_tags($request->description),
        ]);

        return (new LogDescriptionResource($new_item))
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                'message' => 'Successfully store log description.'
            ])->response();
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
    public function update(Request $request):AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);
        $log_description_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$log_description_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->module);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->log_descriptions !== null || $request->log_descriptions > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
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
        $log_description_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;
    
        if (!$log_description_ids && !$query) {
            $response = ["message" => "Invalid request."];
    
            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "meta" => MetadataComposerHelper::compose('delete', $this->module)
                ];
            }
    
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if ($log_description_ids) {
            $log_description_ids = is_array($log_description_ids) 
                ? $log_description_ids 
                : (str_contains($log_description_ids, ',') 
                    ? explode(',', $log_description_ids) 
                    : [$log_description_ids]
                  );
    
            $log_description_ids = array_filter(array_map('intval', $log_description_ids));
            
            if (empty($log_description_ids)) {
                return response()->json(
                    ["message" => "Invalid log description ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $log_descriptions = LogDescription::whereIn('id', $log_description_ids)
                ->whereNull('deleted_at')
                ->get();
    
            if ($log_descriptions->isEmpty()) {
                return response()->json(
                    ["message" => "No active log descriptions found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            $found_ids = $log_descriptions->pluck('id')->toArray();
            
            $deleted_count = LogDescription::whereIn('id', $found_ids)->delete();
    
            return response()->json([
                "message" => "Successfully deleted {$deleted_count} log description(s).",
                "deleted_ids" => $found_ids,
                "count" => $deleted_count
            ], Response::HTTP_OK);
        }
    
        $log_descriptions = LogDescription::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($log_descriptions->count() > 1) {
            return response()->json([
                'data' => $log_descriptions,
                'message' => "Query matches multiple log descriptions. Please specify IDs directly.",
                'suggestion' => "Use ?id parameter for bulk operations or refine your query."
            ], Response::HTTP_CONFLICT);
        }

        $log_description = $log_descriptions->first();

        if (!$log_description) {
            return response()->json(
                ["message" => "No active log description found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $log_description->delete();

        return response()->json([
            "message" => "Successfully deleted log description.",
            "deleted_id" => $log_description->id,
            "description" => $log_description->description // Optional: include relevant info
        ], Response::HTTP_OK);
    }
}
