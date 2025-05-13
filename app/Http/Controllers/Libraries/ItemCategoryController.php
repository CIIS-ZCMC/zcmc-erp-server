<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemCategoryRequest;
use App\Http\Resources\ItemCategoryResource;
use App\Http\Resources\ItemCategoryTrashResource;
use App\Imports\ItemCategoriesImport;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Item Category",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "name", type: "string"),
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
class ItemCategoryController extends Controller
{
    private $is_development;

    private $module = 'item-categories';
    
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    // Protected Function
    protected function cleanData(array $data): array
    {
        $cleanData = [];
        
        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }
        
        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }
        
        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }
        
        if (isset($data['item_category_id'])) {
            $cleanData['item_category_id'] = strip_tags($data['item_category_id']);
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

        $results = ItemCategory::search($searchTerm)->paginate(perPage: $perPage, page: $page);

        return ItemCategoryResource::collection($results)
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
        $objective_success_indicator = ItemCategory::all();

        return ItemCategoryResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully retrieve all records.'
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
        
        $objective_success_indicator = ItemCategory::paginate($perPage, ['*'], 'page', $page);

        return ItemCategoryResource::collection($objective_success_indicator)
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

    protected function singleRecord($item_category_id, $start):JsonResponse
    {
        $itemUnit = ItemCategory::find($item_category_id);
            
        if (!$itemUnit) {
            return response()->json(["message" => "Item category not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ItemCategoryResource($itemUnit))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start):ItemCategoryResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = ItemCategory::whereIn('name', collect($request->item_categories)->pluck('name'))
            ->orWhereIn('code', collect($request->item_categories)->pluck('code'))
            ->orWhereIn('description', collect($request->item_categories)->pluck('description'))
            ->get(['name', 'code', 'description'])->toArray();

        // Convert existing items into a searchable format
        $existing_names = array_column($existing_items, 'name');
        $existing_codes = array_column($existing_items, 'code');
        $existing_description = array_column($existing_items, 'description');

        $failed_store = [];
        $toUpdate = [];

        foreach ($request->item_categories as $item_category) {
            if (!in_array($item_category['name'], $existing_names) && !in_array($item_category['code'], $existing_codes) && !in_array($item_category['description'], $existing_description)) {
                $clean = [
                    "name" => strip_tags($item_category['name']),
                    "code" => strip_tags($item_category['code']),
                    "description" => isset($item_category['description']) ? strip_tags($item_category['description']) : null,
                    "created_at" => now(),
                    "updated_at" => now()
                ];

                $hasItemCategoryId = is_array($item_category) 
                    ? array_key_exists('item_category_id', $item_category)
                    : (is_object($item_category) && property_exists($item_category, 'item_category_id'));

                if ($hasItemCategoryId) {
                    $item_category_exist = ItemCategory::find($item_category['item_category_id']);
                    
                    if(!$item_category_exist){
                        $failed_store[] = [
                            "issue" => "Item category not found.",
                            "data" => $item_category
                        ];
                        continue;
                    }

                    $toUpdate[] = [
                        'name' => $item_category['name'],
                        'item_category_id' => $item_category['item_category_id']
                    ];
                }

                $cleanData[] = $clean;
            }
        }

        if (empty($cleanData) && count($existing_items) > 0) {
            return response()->json([
                'data' => $existing_items,
                'message' => "Failed to bulk insert all item categories already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ItemCategory::insert($cleanData);

        if(!empty($toUpdate)){
            foreach ($toUpdate as $to_update) {
                ItemCategory::where('name', $to_update['name'])
                    ->update(['item_category_id' => $to_update['item_category_id']]);
            }
        }

        $latest_item_units = ItemCategory::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ItemCategoryResource::collection($latest_item_units)
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "existings" => $existing_items,
                    "failed_store" => $failed_store
                ],
                "message" => "Successfully store data."
            ]);
    }
    
    protected function bulkUpdate(Request $request, $start):AnonymousResourceCollection|JsonResponse
    {
        $item_category_ids = $request->query('id') ?? null;

        if (count($item_category_ids) !== count($request->input('item_categories'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of item categories provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_item_units = [];
        $errors = [];
    
        foreach ($item_category_ids as $index => $id) {
            $item_unit = ItemCategory::find($id);
            
            if (!$item_unit) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanData($request->input('item_categories')[$index]);
            $item_unit->update($cleanData);
            $updated_item_units[] = $item_unit;
        }
    
        if (!empty($errors)) {
            return ItemCategoryResource::collection($updated_item_units)
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
        
        return ItemCategoryResource::collection($updated_item_units)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|ItemCategoryResource|JsonResponse
    {
        $item_category_ids = $request->query('id') ?? null;
    
        // Handle single update
        $item_category = ItemCategory::find($item_category_ids[0]);
        
        if (!$item_category) {
            return response()->json([
                "message" => "Item category not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanData($request->all());
        $item_category->update($cleanData);

        return (new ItemCategoryResource($item_category))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update item category record.'
            ])->response();
    }
    
    #[OA\Get(
        path: '/api/item-categories/template',
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
            'Content-Disposition' => 'attachment; filename="item_categories_template.csv"',
        ];
        
        $columns = ['name', 'code', 'description'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
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
            $import = new ItemCategoriesImport;
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getRowCount() - count($import->failures());
            $failures = $import->failures();
            
            return response()->json([
                'message' => "$successCount item categories imported successfully.",
                'success_count' => $successCount,
                'failure_count' => count($failures),
                'failures' => $failures->map(function($failure) {
                    return [
                        'row' => $failure->row(),
                        'errors' => $failure->errors(),
                        'values' => $failure->values()
                    ];
                }),
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error importing file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    protected function cleanCategoryData(array $data): array
    {
        $cleanData = [];
        
        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }
        
        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }
        
        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }
        
        return $cleanData;
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
    public function store(ItemCategoryRequest $request): AnonymousResourceCollection|ItemCategoryResource|JsonResponse
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->item_categories !== null || $request->item_categories > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        if($request->item_category_id)
        {
            $item_category = ItemCategory::find($request->item_category_id);
            
            if(!$item_category){
                return response()->json(['message' => "Item cateogry doesn't exist"], Response::HTTP_NOT_FOUND);
            }

            $cleanData["item_category_id"] = $item_category->id;
        }

        $new_item = itemCategory::create($cleanData);
        
        return (new ItemCategoryResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
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
    public function update(Request $request): AnonymousResourceCollection|ItemCategoryResource|JsonResource|JsonResponse
    {
        $start = microtime(true);
        $item_unit_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$item_unit_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->methods, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->item_categories !== null && $request->item_categories > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = ItemCategory::onlyTrashed();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
        }
        
        return ItemCategoryTrashResource::collection(ItemCategory::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    #[OA\Put(
        path: "/api/item-categories/{id}/restore",
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
    public function restore($id, Request $request)
    {
        ItemCategory::withTrashed()->where('id', $id)->restore();

        return (new ItemCategoryResource(ItemCategory::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
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
        $item_category_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$item_category_ids && !$query) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "meta" => MetadataComposerHelper::compose('delete', $this->module, $this->is_development)
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($item_category_ids) {
            $item_category_ids = is_array($item_category_ids) 
                ? $item_category_ids 
                : (str_contains($item_category_ids, ',') 
                    ? explode(',', $item_category_ids) 
                    : [$item_category_ids]
                );

            // Convert all IDs to integers and filter invalid ones
            $item_category_ids = array_filter(array_map('intval', $item_category_ids));

            if (empty($item_category_ids)) {
                return response()->json(["message" => "Invalid ID format."], Response::HTTP_BAD_REQUEST);
            }

            $item_categories = ItemCategory::whereIn('id', $item_category_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($item_categories->isEmpty()) {
                return response()->json(["message" => "No active records found for the given IDs."], Response::HTTP_NOT_FOUND);
            }

            // Get only the IDs that were actually found and not deleted
            $found_ids = $item_categories->pluck('id')->toArray();
            
            // Soft delete only the found records
            ItemCategory::whereIn('id', $found_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " record(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK);
        }

        $item_categories = ItemCategory::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($item_categories->count() > 1) {
            return response()->json([
                'data' => $item_categories,
                'message' => "Request would affect multiple records. Please specify IDs directly."
            ], Response::HTTP_CONFLICT);
        }

        $item_category = $item_categories->first();

        if (!$item_category) {
            return response()->json(["message" => "No active record found matching query."], Response::HTTP_NOT_FOUND);
        }

        $item_category->delete();
        
        return response()->json([
            "message" => "Successfully deleted record.",
            "deleted_id" => $item_category->id
        ], Response::HTTP_OK);
    }
}
