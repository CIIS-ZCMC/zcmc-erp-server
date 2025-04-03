<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadCheckForMalwareAttack;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemDuplicateResource;
use App\Http\Resources\ItemResource;
use App\Models\FileRecord;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\ItemClassification;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    private $is_development;

    private $module = 'items';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanItemData(array $data): array
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
            $cleanData['item_classification_id'] = (int) $data['item_classification_id'];
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
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?item_id=[primary-key]",
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
                $metadata['fields'] = ["type"];
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

            $metadata["fields"] = ["type"];
        }

        return $metadata;
    }

    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        $page = $request->query('page') > 0 ? $request->query('page') : 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $item_id = $request->query('item_id') ?? null;

        if ($item_id) {
            $item = Item::find($item_id);

            if (!$item) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => new ItemResource($item),
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
                $items = Item::select('id', 'name', 'estimated_budget')
                    ->where('name', 'like', "%" . $search . "%")
                    ->where("deleted_at", NULL)->get();

                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

                if ($this->is_development) {
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }

                return response()->json([
                    "data" => ItemResource::collection($items),
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $items = Item::where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if ($this->is_development) {
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }

            return response()->json([
                "data" => ItemResource::collection($items),
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }


        if ($search !== null) {
            if ($last_id === 0 || $page_item != null) {
                $items = Item::where('name', 'like', '%' . $search . '%')
                    ->where('id', '>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if (count($items) === 0) {
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

                $allIds = Item::where('name', 'like', '%' . $search . '%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);

                $pagination_helper = new PaginationHelper('items', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination($page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);

                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => ItemResource::collection($items),
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
            $items = Item::where('name', 'like', '%' . $search . '%')
                ->where('id', '>', $last_id)
                ->orderBy('id')->limit($per_page)->get();

            // Return the response
            return response()->json([
                'data' => ItemResource::collection($items),
                'metadata' => []
            ], Response::HTTP_OK);
        }

        $total_page = Item::all()->pluck('id')->chunk($per_page);
        $items = Item::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));

        $pagination_helper = new PaginationHelper($this->module, $page, $per_page, $total_page > 10 ? 10 : $total_page);

        return response()->json([
            "data" => ItemResource::collection($items),
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(ItemRequest $request):Response
    {
        $base_message = "Successfully created items";

        // Bulk Insert
        if ($request->items !== null || $request->items > 1) {
            $existing_items = [];
            $existing_items = Item::whereIn('name', collect($request->items)->pluck('name'))
                ->whereIn('estimated_budget', collect($request->items)->pluck('estimated_budget'))
                ->whereIn('item_unit_id', collect($request->items)->pluck('item_unit_id'))
                ->whereIn('item_category_id', collect($request->items)->pluck('item_category_id'))
                ->whereIn('item_classification_id', collect($request->items)->pluck('item_classification_id'))
                ->get(['name'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');
            $existing_estimated_budget = array_column($existing_items, 'estimated_budget');
            $existing_item_unit_id = array_column($existing_items, 'item_unit_id');
            $existing_item_category_id = array_column($existing_items, 'item_category_id');
            $existing_item_classification_id = array_column($existing_items, 'item_classification_id');

            if (!empty($existing_items)) {
                $existing_item_collection = Item::whereIn("name", $existing_names)
                    ->whereIn('estimated_budget', collect($existing_estimated_budget)->pluck('estimated_budget'))
                    ->whereIn('item_unit_id', collect($existing_item_unit_id)->pluck('item_unit_id'))
                    ->whereIn('item_category_id', collect($existing_item_category_id)->pluck('item_category_id'))
                    ->whereIn('item_classification_id', collect($existing_item_classification_id)->pluck('item_classification_id'))->get();

                $existing_items = ItemDuplicateResource::collection($existing_item_collection);
            }

            foreach ($request->items as $item) {
                $is_valid_unit_id = ItemUnit::find($item['item_unit_id']);
                $is_valid_category_id = ItemCategory::find($item['item_category_id']);
                $is_valid_classification_id = ItemClassification::find($item['item_classification_id']);

                if ($is_valid_unit_id && $is_valid_category_id && $is_valid_classification_id) {
                    if (
                        !in_array($item['name'], $existing_names) && !in_array($item['estimated_budget'], $existing_estimated_budget)
                        && !in_array($item['item_unit_id'], $existing_item_unit_id) && !in_array($item['item_category_id'], $existing_item_category_id)
                        && !in_array($item['item_classification_id'], $existing_item_classification_id)
                    ) {
                        $cleanData[] = [
                            "name" => strip_tags($item['name']),
                            "code" => strip_tags($item['code']),
                            "variant" => strip_tags($item['variant']),
                            "estimated_budget" => strip_tags($item['estimated_budget']),
                            "item_unit_id" => strip_tags($item['item_unit_id']),
                            "item_category_id" => strip_tags($item['item_category_id']),
                            "item_classification_id" => strip_tags($item['item_classification_id']),
                            "created_at" => now(),
                            "updated_at" => now()
                        ];
                    }
                    continue;
                }

                return response()->json([
                    "message" => "Invalid data given.",
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_items,
                    'message' => "Failed to bulk insert all items already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Item::insert($cleanData);

            $latest_items = Item::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_items) > 1 ? $base_message . "s record" : $base_message . " record.";

            return response()->json([
                "data" => ItemResource::collection($latest_items),
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_items
                ]
            ], Response::HTTP_CREATED);
        }

        $is_valid_unit_id = ItemUnit::find($request->item_unit_id);
        $is_valid_category_id = ItemCategory::find($request->item_category_id);
        $is_valid_classification_id = ItemClassification::find($request->item_classification_id);

        if (!($is_valid_unit_id && $is_valid_category_id && $is_valid_classification_id)) {
            return response()->json([
                "message" => "Invalid data given.",
                "metadata" => [
                    "methods" => ["GET, POST, PUT, DELETE"]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "variant" => strip_tags($request->input('variant')),
            "estimated_budget" => strip_tags($request->input('estimated_budget')),
            "item_unit_id" => strip_tags($request->input('item_unit_id')),
            "item_category_id" => strip_tags($request->input('item_category_id')),
            "item_classification_id" => strip_tags($request->input('item_classification_id')),
        ];

        $new_item = Item::create($cleanData);

        $metadata = ["methods" => ['GET, POST, PUT, DELETE']];

        if($request->hasFile('file'))
        {
            try{
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
                $filePath = $file->storeAs('uploads/items', $hashedFileName, 'public');
    
                $file = FileRecord::create([
                    "item_id" => $new_item->id,
                    'file_path' => $filePath,
                    'original_name' => $file->getClientOriginalName(),
                    'file_hash' => $hashedFileName,
                    'file_size' => $file->getSize(),
                    'file_type' => $fileExtension,
                ]);

                $new_item->update(['image' => $filePath]);
            }catch(\Throwable $th){
                $metadata['error'] = "Failed to save item image.";
            }
        }

        return response()->json([
            "data" => new ItemResource($new_item),
            "message" => $base_message . " record.",
            "metadata" => [
                "methods" => ['GET, POST, PUT, DELETE'],
            ]
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request): Response
    {
        $item_ids = $request->query('id') ?? null;

        // Validate request has IDs
        if (!$item_ids) {
            $response = ["message" => "ID parameter is required."];

            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $item_ids = is_array($item_ids) ? $item_ids : [$item_ids];

        // For bulk update - validate items array matches IDs count
        if ($request->has('items')) {
            if (count($item_ids) !== count($request->input('items'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of items provided.",
                    "metadata" => $this->getMetadata('put')
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $updated_items = [];
            $errors = [];

            foreach ($item_ids as $index => $id) {
                $item = Item::find($id);

                if (!$item) {
                    $errors[] = "Item with ID {$id} not found.";
                    continue;
                }

                $itemData = $request->input('items')[$index];
                $cleanData = $this->cleanItemData($itemData);

                $item->update($cleanData);
                $updated_items[] = $item;
            }

            if (!empty($errors)) {
                return response()->json([
                    "data" => ItemResource::collection($updated_items),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }

            return response()->json([
                "data" => ItemResource::collection($updated_items),
                "message" => "Successfully updated " . count($updated_items) . " items.",
                "metadata" => $this->getMetadata('put')
            ], Response::HTTP_OK);
        }

        // Single item update
        if (count($item_ids) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no items array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Item::find($item_ids[0]);

        if (!$item) {
            return response()->json([
                "message" => "Item not found."
            ], Response::HTTP_NOT_FOUND);
        }

        $cleanData = $this->cleanItemData($request->all());
        $item->update($cleanData);

        $response = [
            "data" => new ItemResource($item),
            "message" => "Item updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $item_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$item_ids && !$query) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('delete')
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($item_ids) {
            $item_ids = is_array($item_ids)
                ? $item_ids
                : (str_contains($item_ids, ',')
                    ? explode(',', $item_ids)
                    : [$item_ids]
                );

            // Ensure all IDs are integers
            $item_ids = array_filter(array_map('intval', $item_ids));

            if (empty($item_ids)) {
                return response()->json(["message" => "Invalid ID format."], Response::HTTP_BAD_REQUEST);
            }

            $items = Item::whereIn('id', $item_ids)->whereNull('deleted_at')->get();

            if ($items->isEmpty()) {
                return response()->json(["message" => "No active records found for the given IDs."], Response::HTTP_NOT_FOUND);
            }

            // Only soft-delete records that were actually found
            $found_ids = $items->pluck('id')->toArray();
            Item::whereIn('id', $found_ids)->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " record(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK); // Changed from NO_CONTENT to OK to allow response body
        }

        $items = Item::where($query)->whereNull('deleted_at')->get();

        if ($items->count() > 1) {
            return response()->json([
                'data' => $items,
                'message' => "Request would affect multiple records. Please specify IDs directly."
            ], Response::HTTP_CONFLICT);
        }

        $item = $items->first();

        if (!$item) {
            return response()->json(["message" => "No active record found matching query."], Response::HTTP_NOT_FOUND);
        }

        $item->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted record.",
            "deleted_id" => $item->id
        ], Response::HTTP_OK);
    }
}
