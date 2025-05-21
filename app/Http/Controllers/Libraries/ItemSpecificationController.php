<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Helpers\MetadataComposerHelper;
use App\Http\Resources\ItemSpecificationResource;
use App\Models\ItemSpecification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ItemSpecificationController extends Controller
{
    private $is_development;

    private $module = 'item-specifications';
    
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    // Protected Function
    protected function cleanData(array $data): array
    {
        $cleanData = [];

        if (isset($data['description'])) {
            $cleanData['description'] = strip_tags($data['description']);
        }
        
        if (isset($data['item_id'])) {
            $cleanData['item_id'] = strip_tags($data['item_id']);
        }
        
        if (isset($data['item_request_id'])) {
            $cleanData['item_request_id'] = strip_tags($data['item_request_id']);
        }
        
        if (isset($data['item_specification_id'])) {
            $cleanData['item_specification_id'] = strip_tags($data['item_specification_id']);
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

        $results = ItemSpecification::where('description', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ItemSpecificationResource::collection($results)
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
        $objective_success_indicator = ItemSpecification::all();

        return ItemSpecificationResource::collection($objective_success_indicator)
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
        
        $objective_success_indicator = ItemSpecification::paginate($perPage, ['*'], 'page', $page);

        return ItemSpecificationResource::collection($objective_success_indicator)
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
        $itemUnit = ItemSpecification::find($item_category_id);
            
        if (!$itemUnit) {
            return response()->json(["message" => "Item specification not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ItemSpecificationResource($itemUnit))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start):ItemSpecificationResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = ItemSpecification::whereIn('description', collect($request->item_categories)->pluck('description'))
            ->get(['description'])->toArray();

        $existing_description = array_column($existing_items, 'description');

        $failed_store = [];
        $toUpdate = [];

        foreach ($request->item_specifications as $item_specification) {
            if (!in_array($item_specification['description'], $existing_description)) {
                $clean = [
                    "description" => isset($item_specification['description']) ? strip_tags($item_specification['description']) : null,
                    'item_id' => isset($item_specification['item_id']) ? strip_tags($item_specification['item_id']) : null,
                    'item_request_id' => isset($item_specification['item_request_id']) ? strip_tags($item_specification['item_request_id']) : null,
                    "created_at" => now(),
                    "updated_at" => now()
                ];

                $hasItemSpecificationId = is_array($item_specification) 
                    ? array_key_exists('item_specification_id', $item_specification)
                    : (is_object($item_specification) && property_exists($item_specification, 'item_specification_id'));

                if ($hasItemSpecificationId) {
                    $item_specification_exist = ItemSpecification::find($item_specification['item_specification_id']);
                    
                    if(!$item_specification_exist){
                        $failed_store[] = [
                            "issue" => "Item specification not found.",
                            "data" => $item_specification
                        ];
                        continue;
                    }

                    $toUpdate[] = [
                        'description' => $item_specification['description'],
                        'item_specification_id' => $item_specification['item_specification_id']
                    ];
                }

                $cleanData[] = $clean;
            }
        }

        if (empty($cleanData) && count($existing_items) > 0) {
            return response()->json([
                'data' => $existing_items,
                'message' => "Failed to bulk insert all item specification already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ItemSpecification::insert($cleanData);

        if(!empty($toUpdate)){
            foreach ($toUpdate as $to_update) {
                ItemSpecification::where('description', $to_update['description'])
                    ->update(['item_specification_id' => $to_update['item_specification_id']]);
            }
        }

        $latest_item_units = ItemSpecification::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ItemSpecificationResource::collection($latest_item_units)
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
        $item_specification_ids = $request->query('id') ?? null;

        if (count($item_specification_ids) !== count($request->input('item_specifications'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of item categories provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_item_units = [];
        $errors = [];
    
        foreach ($item_specification_ids as $index => $id) {
            $item_unit = ItemSpecification::find($id);
            
            if (!$item_unit) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanData($request->input('item_specifications')[$index]);
            $item_unit->update($cleanData);
            $updated_item_units[] = $item_unit;
        }
    
        if (!empty($errors)) {
            return ItemSpecificationResource::collection($updated_item_units)
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
        
        return ItemSpecificationResource::collection($updated_item_units)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|ItemSpecificationResource|JsonResponse
    {
        $item_specification_ids = $request->query('id') ?? null;
    
        // Handle single update
        $item_specification = ItemSpecification::find($item_specification_ids[0]);
        
        if (!$item_specification) {
            return response()->json([
                "message" => "Item specification not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanData($request->all());
        $item_specification->update($cleanData);

        return (new ItemSpecificationResource($item_specification))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update item specification record.'
            ])->response();
    }

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

    public function store(Request $request)
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->item_specifications !== null || $request->item_specifications > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "description" => strip_tags($request->input('description')),
        ];

        if($request->item_specification_id)
        {
            $item_category = ItemSpecification::find($request->item_specification_id);
            
            if(!$item_category){
                return response()->json(['message' => "Item specification doesn't exist"], Response::HTTP_NOT_FOUND);
            }

            $cleanData["item_specification_id"] = $item_category->id;
        }

        $new_item = ItemSpecification::create($cleanData);
        
        return (new ItemSpecificationResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ])->response();
    }

    public function update(Request $request, ItemSpecification $itemSpecification)
    {
        $start = microtime(true);
        $item_specification_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$item_specification_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->methods, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->item_specifications !== null && $request->item_specifications > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = ItemSpecification::onlyTrashed();

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }
        
        return ItemSpecificationResource::collection(ItemSpecification::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        ItemSpecification::withTrashed()->where('id', $id)->restore();

        return (new ItemSpecificationResource(ItemSpecification::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
    }

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

            $item_categories = ItemSpecification::whereIn('id', $item_category_ids)
                ->whereNull('deleted_at')->get();

            if ($item_categories->isEmpty()) {
                return response()->json(["message" => "No active records found for the given IDs."], Response::HTTP_NOT_FOUND);
            }

            // Get only the IDs that were actually found and not deleted
            $found_ids = $item_categories->pluck('id')->toArray();
            
            // Soft delete only the found records
            ItemSpecification::whereIn('id', $found_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " record(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK);
        }

        $item_categories = ItemSpecification::where($query)
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
