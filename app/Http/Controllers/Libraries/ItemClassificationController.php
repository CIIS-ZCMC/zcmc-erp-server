<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemClassificationRequest;
use App\Http\Resources\ItemClassificationDuplicateResource;
use App\Http\Resources\ItemClassificationResource;
use App\Http\Resources\ItemClassificationTrashResource;
use App\Imports\ItemClassificationsImport;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ItemClassificationController extends Controller
{
    private $is_development;

    private $module = 'item-classifications';
    
    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanData(array $data): array
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

        $results = ItemClassification::search($searchTerm)->paginate(
            perPage: $perPage,
            page: $page
        );

        return ItemClassificationResource::collection($results)
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
        $item_classification = ItemClassification::all();

        return ItemClassificationResource::collection($item_classification)
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
        
        $item_classification = ItemClassification::paginate($perPage, ['*'], 'page', $page);

        return ItemClassificationResource::collection($item_classification)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'pagination' => [
                        'total' => $item_classification->total(),
                        'per_page' => $item_classification->perPage(),
                        'current_page' => $item_classification->currentPage(),
                        'last_page' => $item_classification->lastPage(),
                    ]
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }     
    protected function singleRecord($item_unit_id, $start):JsonResponse
    {
        $itemUnit = ItemClassification::find($item_unit_id);
            
        if (!$itemUnit) {
            return response()->json(["message" => "Item classification not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ItemClassificationResource($itemUnit))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start):ItemClassificationResource|AnonymousResourceCollection|JsonResponse  
    {
        $existing_items = ItemClassification::whereIn('name', collect($request->item_classifications)->pluck('name'))
        ->orWhereIn('code', collect($request->item_classifications)->pluck('code'))
        ->orWhereIn('description', collect($request->item_classifications)->pluck('description'))
        ->get(['name', 'code'])->toArray();

        // Convert existing items into a searchable format
        $existing_names = array_column($existing_items, 'name');
        $existing_codes = array_column($existing_items, 'code');
        $existing_description = array_column($existing_items, 'description');

        foreach ($request->item_classifications as $item) {
            if (!in_array($item['name'], $existing_names) && !in_array($item['code'], $existing_codes) && !in_array($item['description'], $existing_description)) {
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
                'message' => "Failed to bulk insert all item classifications already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ItemClassification::insert($cleanData);

        $latest_item_classifications = ItemClassification::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ItemClassificationResource::collection($latest_item_classifications)
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
        $item_classification_ids = $request->query('id') ?? null;

        if (count($item_classification_ids) !== count($request->input('item_classifications'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of item classifications provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_item_classifications = [];
        $errors = [];
    
        foreach ($item_classification_ids as $index => $id) {
            $item_unit = ItemClassification::find($id);
            
            if (!$item_unit) {
                $errors[] = "Log description with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanData($request->input('item_classifications')[$index]);
            $item_unit->update($cleanData);
            $updated_item_classifications[] = $item_unit;
        }
    
        if (!empty($errors)) {
            return ItemClassificationResource::collection($updated_item_classifications)
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
        
        return ItemClassificationResource::collection($updated_item_classifications)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start): JsonResource|ItemClassificationResource|JsonResponse
    {
        $item_classification_ids = $request->query('id') ?? null;
        
        $item_unit = ItemClassification::find($item_classification_ids[0]);
        
        if (!$item_unit) {
            return response()->json([
                "message" => "Item classification not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanData($request->all());
        $item_unit->update($cleanData);

        return (new ItemClassificationResource($item_unit))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update item classification record.'
            ])->response();
    }
    
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="item_classifications_template.csv"',
        ];
        
        $columns = ['name', 'code', 'description'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, $columns);
            
            fputcsv($file, [
                'Sample Name',
                'SAMPLE-CODE',
                'Optional description'
            ]);
            
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
            $import = new ItemClassificationsImport();
            Excel::import($import, $request->file('file'));
            
            $successCount = $import->getRowCount() - count($import->failures());
            $failures = $import->failures();
            
            return response()->json([
                'message' => "$successCount item classifications imported successfully.",
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

    public function store(ItemClassificationRequest $request)
    {
        $start = microtime(true);

        // Bulk Insert
        if ($request->item_classifications !== null || $request->item_classifications > 1) {
            return $this->bulkStore($request, $start);
        }

        // Single insert
        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = ItemClassification::create($cleanData);
        
        return (new ItemClassificationResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ])->response();
    }

    public function update(Request $request): AnonymousResourceCollection|ItemClassificationResource|JsonResource|JsonResponse    
    {
        $start = microtime(true);
        $item_classification_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$item_classification_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->methods, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->item_classifications !== null || $request->item_classifications > 1) {
            return $this->bulkUpdate($request, $start);
        }
    
        return $this->singleRecordUpdate($request, $start);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = ItemClassification::onlyTrashed();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        }
        
        return ItemClassificationResource::collection(ItemClassification::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        ItemClassification::withTrashed()->where('id', $id)->restore();

        return (new ItemClassificationTrashResource(ItemClassification::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
    }
    
    public function destroy(Request $request): Response
    {
        $item_classification_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;
    
        if (!$item_classification_ids && !$query) {
            $response = ["message" => "Invalid request."];
    
            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "meta" => MetadataComposerHelper::compose('delete', $this->module, $this->is_development)
                ];
            }
    
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if ($item_classification_ids) {
            $item_classification_ids = is_array($item_classification_ids)
                ? $item_classification_ids
                : (str_contains($item_classification_ids, ',')
                    ? explode(',', $item_classification_ids)
                    : [$item_classification_ids]
                  );
    
            // Convert and validate IDs
            $item_classification_ids = array_filter(array_map('intval', $item_classification_ids));
            
            if (empty($item_classification_ids)) {
                return response()->json(["message" => "Invalid ID format provided."], Response::HTTP_BAD_REQUEST);
            }
    
            // Get only active records
            $item_classifications = ItemClassification::whereIn('id', $item_classification_ids)
                ->whereNull('deleted_at')
                ->get();
    
            if ($item_classifications->isEmpty()) {
                return response()->json(
                    ["message" => "No active classifications found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            // Get only IDs that were actually found
            $found_ids = $item_classifications->pluck('id')->toArray();
            
            // Perform soft delete
            ItemClassification::whereIn('id', $found_ids)->delete();
    
            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " classification(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK);
        }
    
        // Ensure we only work with active records
        $item_classifications = ItemClassification::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($item_classifications->count() > 1) {
            return response()->json([
                'data' => $item_classifications,
                'message' => "Query matches multiple records. Please specify IDs directly."
            ], Response::HTTP_CONFLICT);
        }

        $item_classification = $item_classifications->first();

        if (!$item_classification) {
            return response()->json(
                ["message" => "No active classification found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $item_classification->delete();

        return response()->json([
            "message" => "Successfully deleted classification.",
            "deleted_id" => $item_classification->id
        ], Response::HTTP_OK);
    }
}
