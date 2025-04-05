<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Requests\ItemUnitGetRequest;
use App\Http\Requests\ItemUnitRequest;
use App\Http\Resources\ItemRequestResource;
use App\Http\Resources\ItemUnitResource;
use App\Imports\ItemUnitsImport;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\Response;

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

    // Public functions
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
                    "existings" => ItemUnitResource::collection($existing_items),
                ],
                "message" => "Successfully store data."
            ]);
    }

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
    
        return $this->singleRecordUpdate($request, $start);
    }

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
