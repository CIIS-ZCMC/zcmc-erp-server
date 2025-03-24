<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemCategoryRequest;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemCategoryController extends Controller
{
    private $is_development;

    private $module = 'item-categories';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        $page = $request->query('page') > 0? $request->query('page'): 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $item_category_id = $request->query('item_category_id') ?? null;

        if($item_category_id){
            $item_category = ItemCategory::find($item_category_id);

            if(!$item_category){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => [
                        "methods" => "[GET, POST, PUT, DELETE]",
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_category_id=[primary-key]",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                        ]
                    ]
                ]);
            }

            return response()->json([
                'data' => $item_category,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "urls" => [
                        env("SERVER_DOMAIN")."/api/".$this->module."?item_category_id=[primary-key]",
                        env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                        env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                        env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                    ]
                ]
            ], Response::HTTP_OK);
        }

        if($page < 0 || $per_page < 0){
            $response = ["message" => "Invalid request."];
            
            if($this->is_development){
                $response = [
                    "message" => "Invalid value of parameters",
                    "metadata" => [
                        "methods" => "[GET]",
                        "modes" => ["pagination", "selection"],
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_category_id=[primary-key]",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                        ]
                    ]
                ];
            }

            return response()->json([$response], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$page && !$per_page){
            $response = ["message" => "Invalid request."];

            if($this->is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => [
                        "methods" => "[GET]",
                        "modes" => ["pagination", "selection"],
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_category_id=[primary-key]",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                        ]
                    ]
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle return for selection record
        if($mode === 'selection'){
            if($search !== null){
                $item_categories = ItemCategory::select('id','name','code')
                    ->where('name', 'like', '%'.$search.'%')
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $item_categories,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $item_categories = ItemCategory::select('id','name','code')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $item_categories,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $item_categories = ItemCategory::where('name', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();
                    

                if(count($item_categories)  === 0){
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

                $allIds = ItemCategory::where('name', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('item-categories', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $item_categories,
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

            $item_categories = ItemCategory::where('name', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')
                ->limit($per_page)
                ->get();

            // Return the response
            return response()->json([
                'data' => $item_categories,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = ItemCategory::all()->pluck('id')->chunk($per_page);
        $item_categories = ItemCategory::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $item_categories,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(ItemCategoryRequest $request)
    {
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->item_categories !== null || $request->item_categories > 1) {
            $existing_items = ItemCategory::whereIn('name', collect($request->item_categories)->pluck('name'))
                ->orWhereIn('code', collect($request->item_categories)->pluck('code'))
                ->get(['name', 'code'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');
            $existing_codes = array_column($existing_items, 'code');

            foreach ($request->item_categories as $item) {
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
                    'message' => "Failed to bulk insert all item categories already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            ItemCategory::insert($cleanData);

            $latest_item_categories = ItemCategory::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_item_categories) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_item_categories,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_items
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = ItemCategory::create([
            "name" => strip_tags($request->name),
            "code" => strip_tags($request->code),
            "description" => strip_tags($request->description),
        ]);

        return response()->json([
            "data" => $new_item,
            "message" => $base_message." record.",
            "metadata" => [
                "methods" => ['GET, POST, PUT, DELET'],
            ]
        ], Response::HTTP_CREATED);
    }
    public function update(Request $request): Response
    {
        $item_category_ids = $request->query('id') ?? null;
    
        // Validate ID parameter exists
        if (!$item_category_ids) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['metadata'] = [
                    "methods" => "[PUT]",
                    "formats" => [
                        env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                        env("SERVER_DOMAIN")."/api/".$this->module."?id[]=1&id[]=2"
                    ],
                    "required_fields" => ["name", "code"]
                ];
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        // Convert single ID to array for consistent processing
        $item_category_ids = is_array($item_category_ids) ? $item_category_ids : [$item_category_ids];
    
        // Handle bulk update
        if ($request->has('item_categories')) {
            return $this->handleBulkUpdate($item_category_ids, $request);
        }
    
        // Handle single update
        return $this->handleSingleUpdate($item_category_ids[0], $request);
    }
    
    protected function handleBulkUpdate(array $ids, Request $request): Response
    {
        if (count($ids) !== count($request->input('item_categories'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of categories provided.",
                "metadata" => $this->getMetadata()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        $updated_categories = [];
        $errors = [];
    
        foreach ($ids as $index => $id) {
            $category = ItemCategory::find($id);
            
            if (!$category) {
                $errors[] = "Category with ID {$id} not found.";
                continue;
            }
    
            $cleanData = $this->cleanCategoryData($request->input('item_categories')[$index]);
            $category->update($cleanData);
            $updated_categories[] = $category;
        }
    
        if (!empty($errors)) {
            return response()->json([
                "data" => $updated_categories,
                "message" => "Partial update completed with errors.",
                "errors" => $errors,
                "metadata" => $this->getMetadata()
            ], Response::HTTP_MULTI_STATUS);
        }
    
        return response()->json([
            "data" => $updated_categories,
            "message" => "Successfully updated ".count($updated_categories)." categories.",
            "metadata" => $this->getMetadata()
        ], Response::HTTP_OK);
    }
    
    protected function handleSingleUpdate(int $id, Request $request): Response
    {
        $category = ItemCategory::find($id);
        
        if (!$category) {
            return response()->json([
                "message" => "Category not found."
            ], Response::HTTP_NOT_FOUND);
        }
    
        $cleanData = $this->cleanCategoryData($request->all());
        $category->update($cleanData);
    
        return response()->json([
            "data" => $category,
            "message" => "Category updated successfully.",
            "metadata" => $this->getMetadata()
        ], Response::HTTP_OK);
    }
    
    protected function cleanCategoryData(array $data): array
    {
        return array_filter([
            'name' => isset($data['name']) ? strip_tags($data['name']) : null,
            'code' => isset($data['code']) ? strip_tags($data['code']) : null,
            'description' => isset($data['description']) ? strip_tags($data['description']) : null
        ], function($value) {
            return $value !== null;
        });
    }
    
    protected function getMetadata(): array
    {
        $metadata = ["methods" => "[PUT]"];
        
        if ($this->is_development) {
            $metadata["formats"] = [
                env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                env("SERVER_DOMAIN")."/api/".$this->module."?id[]=1&id[]=2"
            ];
            $metadata['fields'] = ["name", "code"];
        }
        
        return $metadata;
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
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                        "formats" => [
                            env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                            env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                            env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
                        ],
                        "fields" => ["code"]
                    ]
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        if ($item_category_ids) {
            $item_category_ids = is_array($item_category_ids) ? $item_category_ids : explode(',', $item_category_ids);
            $item_categories = ItemCategory::whereIn('id', $item_category_ids)->where('deleted_at', NULL)->get();

            if ($item_categories->isEmpty()) {
                return response()->json(["message" => "No records found."], Response::HTTP_NOT_FOUND);
            }

            ItemCategory::whereIn('id', $item_category_ids)->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted " . count($item_categories) . " records."
            ], Response::HTTP_NO_CONTENT);
        }

        if ($query) {
            $item_categories = ItemCategory::where($query)->get();

            if ($item_categories->count() > 1) {
                return response()->json([
                    'data' => $item_categories,
                    'message' => "Request has multiple records."
                ], Response::HTTP_CONFLICT);
            }

            $item_category = $item_categories->first();

            if (!$item_category) {
                return response()->json(["message" => "No record found."], Response::HTTP_NOT_FOUND);
            }

            $item_category->update(['deleted_at' => now()]);
        }

        return response()->json(["message" => "Successfully deleted record."], Response::HTTP_NO_CONTENT);
    }
}
