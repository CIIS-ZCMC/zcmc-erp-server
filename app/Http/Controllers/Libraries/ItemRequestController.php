<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Helpers\FileUploadCheckForMalwareAttack;
use App\Helpers\MetadataComposerHelper;
use App\Http\Requests\ItemRequestRequest;
use App\Http\Resources\ItemRequestDuplicateResource;
use App\Http\Resources\ItemRequestResource;
use App\Http\Resources\ItemResource;
use App\Models\FileRecord;
use App\Models\ItemCategory;
use App\Models\ItemRequest;
use App\Models\ItemClassification;
use App\Models\ItemSpecification;
use App\Models\ItemUnit;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ItemRequestController extends Controller
{
    private $is_development;

    private $module = 'item-requests';

    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanData(array $data): array
    {
        $cleanData = [];

        // Only include fields that exist in the request
        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }

        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }

        if (isset($data['variant'])) {
            $cleanData['variant'] = strip_tags($data['variant']);
        }

        if (isset($data['status'])) {
            $cleanData['status'] = strip_tags($data['status']);
        }

        if (isset($data['reason'])) {
            $cleanData['reason'] = strip_tags($data['reason']);
        }

        if (isset($data['estimated_budget'])) {
            $cleanData['estimated_budget'] = filter_var(
                $data['estimated_budget'],
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            );
        }

        if (isset($data['item_unit_id'])) {
            $cleanData['item_unit_id'] = (int) $data['item_unit_id'];
        }

        if (isset($data['item_category_id'])) {
            $cleanData['item_category_id'] = (int) $data['item_category_id'];
        }

        if (isset($data['item_classification_id'])) {
            $item_classification = ItemClassification::find($data['item_classification_id']);

            $cleanData['item_classification_id'] = $item_classification ? (int) $data['item_classification_id'] : null;
        }

        return $cleanData;
    }

    public function approve(ItemRequest $itemRequest, Request $request)
    {
        // Check if user is consolidator

        DB::beginTransaction();

        $cleanData = [
            "name" => $itemRequest->name,
            "code" => $itemRequest->code,
            "estimated_budget" => $itemRequest->estimated_budget,
            "item_unit_id" => $itemRequest->item_unit_id,
            "terminologies_category_id" => $itemRequest->terminology_category_id,
            "item_category_id" => $itemRequest->item_category_id,
            "item_classification_id" => $itemRequest->item_classification_id,
            "image" => $itemRequest->image,
        ];

        $new_item = Item::create($cleanData);

        $specifications = $itemRequest->itemSpecifications;

        foreach ($specifications as $specification) {
            $specification->update(['item_id' => $new_item->id, 'item_request_id' => null]);
        }

        DB::commit();

        return (new ItemResource($new_item))
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                ],
                'message' => 'Item request successfully approved'
            ]);
    }

    protected function search(Request $request, $start): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $searchTerm = '%' . trim($validated['search']) . '%';
        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $results = ItemRequest::where('name', 'like', "%{$searchTerm}%")
            ->orWhere('code', 'like', "%{$searchTerm}%")
            ->orWhere('variant', 'like', "%{$searchTerm}%")
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ItemRequestResource::collection($results)
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
        $objective_success_indicator = ItemRequest::all();

        return ItemRequestResource::collection($objective_success_indicator)
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

        $item_requests = ItemRequest::paginate($perPage, ['*'], 'page', $page);
        return ItemRequestResource::collection($item_requests)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'pagination' => [
                        'total' => $item_requests->total(),
                        'per_page' => $item_requests->perPage(),
                        'current_page' => $item_requests->currentPage(),
                        'last_page' => $item_requests->lastPage(),
                    ]
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }

    protected function singleRecord($item_category_id, $start): JsonResponse
    {
        $itemUnit = ItemRequest::find($item_category_id);

        if (!$itemUnit) {
            return response()->json(["message" => "ItemRequest category not found."], Response::HTTP_NOT_FOUND);
        }

        return (new ItemRequestResource($itemUnit))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    protected function bulkStore(Request $request, $start): ItemRequestResource|AnonymousResourceCollection|JsonResponse
    {
        $user = null;
        $existing_items = [];
        $existing_items = ItemRequest::whereIn('name', collect($request->item_requests)->pluck('name'))
            ->get(['name'])->toArray();

        // Convert existing item_requests into a searchable format
        $existing_names = array_column($existing_items, 'name');

        if (!empty($existing_items)) {
            $existing_item_collection = ItemRequest::whereIn("name", $existing_names)->get();

            $existing_items = ItemRequestDuplicateResource::collection($existing_item_collection);
        }

        foreach ($request->item_requests as $item) {
            if (
                !in_array($item['name'], $existing_names)
            ) {
                $cleanData[] = [
                    "name" => strip_tags($item['name']),
                    "code" => strip_tags($item['code']),
                    "variant" => strip_tags($item['variant']),
                    "estimated_budget" => strip_tags($item['estimated_budget']),
                    "item_unit_id" => $item['item_unit_id'] !== null ?  strip_tags($item['item_unit_id']) : null,
                    "item_category_id" => $item['item_category_id'] !== null ? strip_tags($item['item_category_id']) : null,
                    "item_classification_id" => $item['item_classification_id'] !== null ? strip_tags($item['item_classification_id']) : null,
                    "terminologies_category_id" => strip_tags($item['terminology_category_id']),
                    "requested_by" => $user,
                    'reason' => strip_tags($item['reason']),
                    "created_at" => now(),
                    "updated_at" => now()
                ];

                continue;
            }

            return response()->json([
                "message" => "Data already exist.",
                "meta" => [
                    "methods" => "[GET, PUT, DELETE]",
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($cleanData) && count($existing_items) > 0) {
            return response()->json([
                'data' => $existing_items,
                'message' => "Failed to bulk insert all item_requests already exist.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        ItemRequest::insert($cleanData);

        foreach ($request->item_requests as $item_request) {
            $itemRequest = ItemRequest::where('name', $item_request['name'])->first();
            $item_specifications = $item_request['specifications'];

            foreach ($item_specifications as $specification) {
                ItemSpecification::create([
                    "item_request_id" => $itemRequest->id,
                    "description" => $specification['description']
                ]);
            }
        }
        $latest_item_units = ItemRequest::orderBy('id', 'desc')
            ->limit(count($cleanData))->get()
            ->sortBy('id')->values();

        return ItemRequestResource::collection($latest_item_units)
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "existings" => $existing_items
                ],
                "message" => "Successfully store data."
            ]);
    }

    protected function bulkUpdate(Request $request, $start): AnonymousResourceCollection|JsonResponse
    {
        $item_request_ids = $request->query('id') ?? null;

        if (count($item_request_ids) !== count($request->input('item_requests'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of item_requests provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updated_item_units = [];
        $errors = [];

        foreach ($item_request_ids as $index => $id) {
            $item_unit = ItemRequest::find($id);

            if (!$item_unit) {
                $errors[] = "ItemRequest with ID {$id} not found.";
                continue;
            }

            $cleanData = $this->cleanData($request->input('item_requests')[$index]);
            $item_unit->update($cleanData);
            $updated_item_units[] = $item_unit;
        }

        if (!empty($errors)) {
            return ItemRequestResource::collection($updated_item_units)
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

        return ItemRequestResource::collection($updated_item_units)
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    "url_formats" => MetadataComposerHelper::compose('put', $this->methods, $this->is_development)
                ],
                "message" => "Partial update completed with errors.",
            ]);
    }

    protected function singleRecordUpdate(Request $request, $start, $id): JsonResource|ItemRequestResource|JsonResponse
    {

        // Handle single update
        $item = ItemRequest::find($id);

        if (!$item) {
            return response()->json([
                "message" => "ItemRequest not found."
            ], Response::HTTP_NOT_FOUND);
        }

        $cleanData = $this->cleanData($request->all());
        $item->update($cleanData);

        return (new ItemRequestResource($item))
            ->additional([
                "meta" => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully update item category record.'
            ])->response();
    }

    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
    }

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);
        $item_unit_id = $request->query(key: 'id');
        $search = $request->search;
        $mode = $request->mode;

        if ($item_unit_id) {
            return $this->singleRecord($item_unit_id, $start);
        }

        if ($mode && $mode === 'selection') {
            return $this->all($start);
        }

        if ($search) {
            return $this->search($request, $start);
        }

        return $this->pagination($request, $start);
    }

    public function myItemRequest(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $start = microtime(true);
        $item_unit_id = $request->query(key: 'id');
        $search = $request->search;
        $mode = $request->mode;

        if ($item_unit_id) {
            return ItemRequestResource::collection(ItemRequest::find($item_unit_id))
                ->additional([
                    'meta' => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                    ],
                    'message' => 'Successfully retrieve item request.'
                ]);
        }

        if ($mode && $mode === 'selection') {
            return ItemRequestResource::collection(ItemRequest::where('requested_by', auth()->user()->id)->get())
                ->additional([
                    'meta' => [
                        'methods' => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000),
                    ],
                    'message' => 'Successfully retrieve all records.'
                ]);
        }

        if ($search) {
            $validated = $request->validate([
                'search' => 'required|string|min:2|max:100',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1|max:100'
            ]);

            $searchTerm = '%' . trim($validated['search']) . '%';
            $perPage = $validated['per_page'] ?? 15;
            $page = $validated['page'] ?? 1;

            $results = ItemRequest::where('requested_by', auth()->user()->id)
                ->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('code', 'like', "%{$searchTerm}%")
                ->orWhere('variant', 'like', "%{$searchTerm}%")
                ->paginate(
                    perPage: $perPage,
                    page: $page
                );

            return ItemRequestResource::collection($results)
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

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $item_requests = ItemRequest::where('requested_by', auth()->user()->id)->paginate($perPage, ['*'], 'page', $page);

        return ItemRequestResource::collection($item_requests)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'pagination' => [
                        'total' => $item_requests->total(),
                        'per_page' => $item_requests->perPage(),
                        'current_page' => $item_requests->currentPage(),
                        'last_page' => $item_requests->lastPage(),
                    ]
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }

    public function store(ItemRequestRequest $request): AnonymousResourceCollection|ItemRequestResource|JsonResponse
    {
        $user = null;
        $start = microtime(true);

        // Bulk Insert
        if ($request->item_requests !== null || $request->item_requests > 1) {
            return $this->bulkStore($request, $start);
        }

        $is_valid_unit_id = ItemUnit::find($request->item_unit_id);
        $is_valid_category_id = ItemCategory::find($request->item_category_id);
        $is_valid_classification_id = ItemClassification::find($request->item_classification_id);

        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            // "variant_id" => strip_tags($request->input('variant_id')),
            "estimated_budget" => strip_tags($request->input('estimated_budget')),
            "item_unit_id" => !$is_valid_unit_id ? null :  strip_tags($request->input('item_unit_id')),
            "item_category_id" => !$is_valid_category_id ? null :  strip_tags($request->input('item_category_id')),
            "item_classification_id" => !$is_valid_classification_id ? null : strip_tags($request->input('item_classification_id')),
            "terminologies_category_id" => strip_tags($request->input('terminology_category_id')),
            "requested_by" => $user,
            "reason" => strip_tags($request->input('reason'))
        ];
        $new_item = ItemRequest::create($cleanData);

        $item_specifications = $request->specifications;

        foreach ($item_specifications as $specification) {
            ItemSpecification::create([
                "item_request_id" => $new_item->id,
                "description" => $specification['description']
            ]);
        }

        if ($request->hasFile('file')) {
            try {
                $fileChecker = new FileUploadCheckForMalwareAttack();

                // Check if file is safe
                if (!$fileChecker->isFileSafe($request->file('file'))) {
                    return response()->json([
                        "message" => 'File upload failed security checks'
                    ], Response::HTTP_BAD_REQUEST);
                }

                // File is safe, proceed with saving
                $file = $request->file('file');
                $fileExtension = $file->getClientOriginalExtension();
                $hashedFileName = hash_file('sha256', $file->getRealPath()) . '.' . $fileExtension;

                // Store file with hashed name
                $filePath = $file->storeAs('uploads/item_requests', $hashedFileName, 'public');

                $file = FileRecord::create([
                    "item_id" => $new_item->id,
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'file_hash' => $hashedFileName,
                    'file_size' => $file->getSize(),
                    'file_type' => $fileExtension,
                ]);

                $new_item->update(['image' => $filePath]);
            } catch (\Throwable $th) {
                $metadata['error'] = "Failed to save item image.";
            }
        }

        return (new ItemRequestResource($new_item))
            ->additional([
                'meta' => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                "message" => "Successfully store data."
            ]);
    }

    public function update(Request $request, $id): AnonymousResourceCollection|ItemRequestResource|JsonResource|JsonResponse
    {
        $start = microtime(true);

        // Validate ID parameter exists
        if (!$id) {
            $response = ["message" => "ID parameter is required."];

            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->methods, $this->is_development);
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Bulk Insert
        if ($request->item_requests !== null && $request->item_requests > 1) {
            return $this->bulkUpdate($request, $start);
        }

        return $this->singleRecordUpdate($request, $start, $id);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = ItemRequest::onlyTrashed();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('variant', 'like', "%{$search}%");
        }

        return ItemRequestResource::collection(ItemRequest::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        ItemRequest::withTrashed()->where('id', $id)->restore();

        return (new ItemRequestResource(ItemRequest::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
    }

    public function destroy(Request $request): Response
    {
        $item_request_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$item_request_ids && !$query) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "meta" => MetadataComposerHelper::compose('delete', $this->module, $this->is_development)
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($item_request_ids) {
            $item_request_ids = is_array($item_request_ids)
                ? $item_request_ids
                : (str_contains($item_request_ids, ',')
                    ? explode(',', $item_request_ids)
                    : [$item_request_ids]
                );

            // Ensure all IDs are integers
            $item_request_ids = array_filter(array_map('intval', $item_request_ids));

            if (empty($item_request_ids)) {
                return response()->json(["message" => "Invalid ID format."], Response::HTTP_BAD_REQUEST);
            }

            $item_requests = ItemRequest::whereIn('id', $item_request_ids)->whereNull('deleted_at')->get();

            if ($item_requests->isEmpty()) {
                return response()->json(["message" => "No active records found for the given IDs."], Response::HTTP_NOT_FOUND);
            }

            // Only soft-delete records that were actually found
            $found_ids = $item_requests->pluck('id')->toArray();
            ItemRequest::whereIn('id', $found_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " record(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK); // Changed from NO_CONTENT to OK to allow response body
        }

        $item_requests = ItemRequest::where($query)->whereNull('deleted_at')->get();

        if ($item_requests->count() > 1) {
            return response()->json([
                'data' => $item_requests,
                'message' => "Request would affect multiple records. Please specify IDs directly."
            ], Response::HTTP_CONFLICT);
        }

        $item = $item_requests->first();

        if (!$item) {
            return response()->json(["message" => "No active record found matching query."], Response::HTTP_NOT_FOUND);
        }

        $item->delete();

        return response()->json([
            "message" => "Successfully deleted record.",
            "deleted_id" => $item->id
        ], Response::HTTP_OK);
    }
}
