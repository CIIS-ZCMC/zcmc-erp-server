<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Requests\ItemUnitGetRequest;
use App\Http\Requests\ItemUnitRequest;
use App\Http\Resources\ItemUnitResource;
use App\Imports\ItemUnitsImport;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Item Unit",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "name", type: "string"),
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
class ItemUnitController extends Controller
{
    private $is_development;

    private $module = 'item-units';
    
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    // Protected Function
    protected function cleanItemUnitData(array $data): array
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
    
    protected function getMetadata($method): array
    {
        if($method === 'get'){
            $meta['methods'] = ["GET, POST, PUT, DELETE"];
            $meta['modes'] = ['selection', 'pagination'];

            if($this->is_development){
                $meta['urls'] = [
                    env("SERVER_DOMAIN")."/api/".$this->module."?item_unit_id=[primary-key]",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $meta;
        }
        
        if($method === 'put'){
            $meta = ["methods" => "[PUT]"];
        
            if ($this->is_development) {
                $meta["urls"] = [
                    env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                    env("SERVER_DOMAIN")."/api/".$this->module."?id[]=1&id[]=2"
                ];
                $meta['fields'] = ["title", "code", "description"];
            }
            
            return $meta;
        }
        
        $meta = ['methods' => ["GET, PUT, DELETE"]];

        if($this->is_development) {
            $meta["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];

            $meta["fields"] =  ["code"];
        }

        return $meta;
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

        $results = ItemUnit::where('code', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ItemUnitResource::collection($results)
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
        $objective_success_indicator = ItemUnit::all();

        return ItemUnitResource::collection($objective_success_indicator)
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
        
        $objective_success_indicator = ItemUnit::paginate($perPage, ['*'], 'page', $page);

        return ItemUnitResource::collection($objective_success_indicator)
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
        $itemUnit = ItemUnit::find($item_unit_id);
            
        if (!$itemUnit) {
            return response()->json(["message" => "Item unit not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ItemUnitResource($itemUnit))
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
        $item_unit_ids = $request->query('id') ?? null;

        if (count($item_unit_ids) !== count($request->input('item_units'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of item units provided.",
                "meta" => $this->getMetadata('put')
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_item_units = [];
        $errors = [];
    
        foreach ($item_unit_ids as $index => $id) {
            $item_unit = ItemUnit::find($id);
            
            if (!$item_unit) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanItemUnitData($request->input('item_units')[$index]);
            $item_unit->update($cleanData);
            $updated_item_units[] = $item_unit;
        }
    
        if (!empty($errors)) {
            return ItemUnitResource::collection($updated_item_units)
                ->additional([
                    "meta" => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                        'issue' => $errors,
                        'url_format' => $this->getMetadata('put'),
                    ],
                    "message" => "Partial update completed with errors.",
                ])
                ->response()
                ->setStatusCode(Response::HTTP_MULTI_STATUS);
        }
        
        return ItemUnitResource::collection($updated_item_units)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'url_format' => $this->getMetadata('put')
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|ItemUnitResource|JsonResponse
    {
        $item_unit_ids = $request->query('id') ?? null;
        
        // Convert single ID to array for consistent processing
        $item_unit_ids = is_array($item_unit_ids) ? $item_unit_ids : [$item_unit_ids];
    
        // Handle bulk update
        if ($request->has('item_units')) {
            $this->bulkUpdate($request, $start);
        }
    
        // Handle single update
        $item_unit = ItemUnit::find($item_unit_ids[0]);
        
        if (!$item_unit) {
            return response()->json([
                "message" => "Item unit not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanItemUnitData($request->all());
        $item_unit->update($cleanData);

        return (new ItemUnitResource($item_unit))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update item unit record.'
            ])->response();
    }
    
    #[OA\Get(
        path: '/api/item-units/template',
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
            'Content-Disposition' => 'attachment; filename="item_units_template.csv"',
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
            $import = new ItemUnitsImport;
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getRowCount() - count($import->failures());
            $failures = $import->failures();
            
            return response()->json([
                'message' => "$successCount item units imported successfully.",
                'success_count' => $successCount,
                'failure_count' => count($failures),
                'failures' => $failures,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error importing file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[OA\Get(
        path: "/api/users",
        summary: "List all users",
        responses: [
            new OA\Response(response: 200, description: "OK"),
            new OA\Response(response: 500, description: "Server Error")
        ]
    )]
    public function index(GetWithPaginatedSearchModeRequest $request): ItemUnitResource|AnonymousResourceCollection|JsonResponse   
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

    protected function bulkStore(Request $request, $start):ItemUnitResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = ItemUnit::whereIn('name', collect($request->item_units)->pluck('name'))
        ->orWhereIn('code', collect($request->item_units)->pluck('code'))
        ->get(['name', 'code'])->toArray();

        // Convert existing items into a searchable format
        $existing_names = array_column($existing_items, 'name');
        $existing_codes = array_column($existing_items, 'code');

        foreach ($request->item_units as $item) {
            if (!in_array($item['name'], $existing_names) && !in_array($item['code'], $existing_codes)) {
                $cleanData[] = [
                    "name" => strip_tags($item['name']),
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
                'message' => "Failed to bulk insert all item units already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ItemUnit::insert($cleanData);

        $latest_item_units = ItemUnit::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ItemUnitResource::collection($latest_item_units)
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "existings" => $existing_items,
                ],
                "message" => "Successfully store data."
            ]);
    }

    #[OA\Post(
        path: '/api/item-units',
        summary: 'Create item unit(s) - single or bulk',
        requestBody: new OA\RequestBody(
            description: 'Item unit data',
            required: true,
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'name',
                                type: 'string',
                                example: 'Piece'
                            ),
                            new OA\Property(
                                property: 'code',
                                type: 'string',
                                example: 'PC'
                            ),
                            new OA\Property(
                                property: 'description',
                                type: 'string',
                                example: 'Individual pieces',
                                nullable: true
                            )
                        ]
                    ),
                    new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'item_units',
                                type: 'array',
                                items: new OA\Items(
                                    properties: [
                                        new OA\Property(
                                            property: 'name',
                                            type: 'string'
                                        ),
                                        new OA\Property(
                                            property: 'code',
                                            type: 'string'
                                        ),
                                        new OA\Property(
                                            property: 'description',
                                            type: 'string',
                                            nullable: true
                                        )
                                    ]
                                ),
                                minItems: 2
                            )
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successfully stored item unit(s)',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/ItemUnit'
                                ),
                                new OA\Property(
                                    property: 'meta',
                                    properties: [
                                        new OA\Property(
                                            property: 'methods',
                                            type: 'array',
                                            items: new OA\Items(type: 'string')
                                        ),
                                        new OA\Property(
                                            property: 'time_ms',
                                            type: 'integer'
                                        )
                                    ]
                                ),
                                new OA\Property(
                                    property: 'message',
                                    type: 'string'
                                )
                            ]
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ItemUnit')
                                ),
                                new OA\Property(
                                    property: 'meta',
                                    properties: [
                                        new OA\Property(
                                            property: 'methods',
                                            type: 'array',
                                            items: new OA\Items(type: 'string')
                                        ),
                                        new OA\Property(
                                            property: 'time_ms',
                                            type: 'integer'
                                        )
                                    ]
                                ),
                                new OA\Property(
                                    property: 'message',
                                    type: 'string'
                                )
                            ]
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
                            additionalProperties: new OA\Property(
                                type: 'array',
                                items: new OA\Items(type: 'string')
                            )
                        )
                    ]
                )
            )
        ],
        tags: ['Item Units']
    )]
    public function store(ItemUnitRequest $request): ItemUnitResource|AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->item_units !== null || $request->item_units > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = ItemUnit::create($cleanData);
        
        return (new ItemUnitResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ])->response();
    }

    #[OA\Put(
        path: '/api/item-units',
        summary: 'Update item unit(s)',
        description: 'Updates one or multiple item units. Requires ID parameter in query string.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                description: 'Comma-separated list of item unit IDs',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: '1,2,3'
                )
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Item unit update data',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Updated Unit Name',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'code',
                        type: 'string',
                        example: 'UPD',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Updated description',
                        nullable: true
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Successfully updated item unit(s)',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/ItemUnitResource'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ItemUnitResource')
                                )
                            ]
                        )
                    ],
                    example: [
                        'data' => [
                            'id' => 1,
                            'name' => 'Updated Unit',
                            'code' => 'UPD',
                            'description' => 'Updated description'
                        ],
                        'meta' => [
                            'methods' => ['PUT'],
                            'time_ms' => 25
                        ],
                        'message' => 'Successfully updated record'
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'ID parameter is required.'
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(
                                    property: 'endpoint',
                                    type: 'string',
                                    example: 'PUT /api/item-units'
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'Item unit not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Item unit not found.'
                        )
                    ]
                )
            )
        ],
        tags: ['Item Units']
    )]
    public function update(Request $request): ItemUnitResource|AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);
        $item_unit_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$item_unit_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = $this->getMetadata('put');
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->item_units !== null || $request->item_units > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    #[OA\Delete(
        path: '/api/item-units',
        summary: 'Delete item unit(s)',
        description: 'Deletes one or multiple item units by ID or query parameters',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                description: 'Single ID or comma-separated list of IDs',
                required: false,
                schema: new OA\Schema(
                    oneOf: [
                        new OA\Schema(type: 'integer', example: 1),
                        new OA\Schema(type: 'string', example: '1,2,3')
                    ]
                )
            ),
            new OA\Parameter(
                name: 'query',
                in: 'query',
                description: 'JSON query for filtering (use with caution)',
                required: false,
                schema: new OA\Schema(type: 'string', example: '{"code":"PC"}')
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Successful deletion',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'message', type: 'string'),
                                new OA\Property(
                                    property: 'deleted_ids',
                                    type: 'array',
                                    items: new OA\Items(type: 'integer'))
                            ],
                            example: [
                                'message' => 'Successfully deleted 3 item unit(s).',
                                'deleted_ids' => [1, 2, 3]
                            ]
                        ),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'message', type: 'string'),
                                new OA\Property(property: 'deleted_id', type: 'integer')
                            ],
                            example: [
                                'message' => 'Successfully deleted item unit.',
                                'deleted_id' => 1
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid ID format',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ],
                    example: [
                        'message' => 'Invalid ID format provided.'
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'No items found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string')
                    ],
                    example: [
                        'message' => 'No active item units found with the provided IDs.'
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_CONFLICT,
                description: 'Query matches multiple records',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'suggestion', type: 'string'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ItemUnit')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            nullable: true,
                            properties: [
                                new OA\Property(property: 'endpoint', type: 'string')
                            ]
                        )
                    ],
                    examples: [
                        'production' => [
                            'value' => [
                                'message' => 'Invalid request.'
                            ]
                        ],
                        'development' => [
                            'value' => [
                                'message' => 'No parameters found.',
                                'meta' => [
                                    'endpoint' => 'DELETE /api/item-units'
                                ]
                            ]
                        ]
                    ]
                )
            )
        ],
        tags: ['Item Units'],
        security: [
            new OA\SecurityScheme(
                securityScheme: 'bearerAuth',
                type: 'http',
                scheme: 'bearer'
            )
        ]
    )]
    public function destroy(Request $request): Response
    {
        $item_unit_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$item_unit_ids && !$query) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "meta" => $this->getMetadata("delete")
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($item_unit_ids) {
            $item_unit_ids = is_array($item_unit_ids) 
                ? $item_unit_ids 
                : (str_contains($item_unit_ids, ',') 
                    ? explode(',', $item_unit_ids) 
                    : [$item_unit_ids]
                );

            $item_unit_ids = array_filter(array_map('intval', $item_unit_ids));
            
            if (empty($item_unit_ids)) {
                return response()->json(
                    ["message" => "Invalid ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $item_units = ItemUnit::whereIn('id', $item_unit_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($item_units->isEmpty()) {
                return response()->json(
                    ["message" => "No active item units found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }

            $found_ids = $item_units->pluck('id')->toArray();
            
            ItemUnit::whereIn('id', $found_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " item unit(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK);
        }

        $item_units = ItemUnit::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($item_units->count() > 1) {
            return response()->json([
                'data' => $item_units,
                'message' => "Query matches multiple records. Please specify IDs directly.",
                'suggestion' => "Use the ID parameter instead for bulk operations."
            ], Response::HTTP_CONFLICT);
        }

        $item_unit = $item_units->first();

        if (!$item_unit) {
            return response()->json(
                ["message" => "No active item unit found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $item_unit->delete();

        return response()->json([
            "message" => "Successfully deleted item unit.",
            "deleted_id" => $item_unit->id
        ], Response::HTTP_OK);
    }
}
