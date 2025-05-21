<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadCheckForMalwareAttack;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemRequestRequest;
use App\Http\Resources\ItemRequestResource;
use App\Http\Resources\ItemResource;
use App\Models\FileRecord;
use App\Models\ItemCategory;
use App\Models\ItemRequest;
use App\Models\ItemClassification;
use App\Models\ItemUnit;
use App\Models\Item;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemRequestController extends Controller
{
    private $is_development;

    private $module = 'item-requests';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanItemRequestData(array $data): array
    {
        $cleanData = [];
        
        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }

        if (isset($data['specs'])) {
            $cleanData['specs'] = strip_tags($data['specs']);
        }
        
        if (isset($data['estimated_budget'])) {
            $cleanData['estimated_budget'] = filter_var(
                $data['estimated_budget'], 
                FILTER_SANITIZE_NUMBER_FLOAT, 
                FILTER_FLAG_ALLOW_FRACTION
            );
        }
        
        if (isset($data['item_unit_id'])) {
            $cleanData['item_unit_id'] = (int)$data['item_unit_id'];
        }
        
        if (isset($data['item_category_id'])) {
            $cleanData['item_category_id'] = (int)$data['item_category_id'];
        }
        
        if (isset($data['item_classification_id'])) {
            $cleanData['item_classification_id'] = (int)$data['item_classification_id'];
        }
        
        return $cleanData;
    }
    
    protected function getMetadata($method): array
    {
        if($method === 'get'){
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if($this->is_development){
                $metadata['urls'] = [
                    env("SERVER_DOMAIN")."/api/".$this->module."?item_id=[primary-key]",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $metadata;
        }
        
        if($method === 'put'){
            $metadata = ["methods" => "[PUT]"];
        
            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                    env("SERVER_DOMAIN")."/api/".$this->module."?id[]=1&id[]=2"
                ];
                $metadata['fields'] = ["type"];
            }
            
            return $metadata;
        }
        
        $metadata = ['methods' => ["GET, PUT, DELETE"]];

        if($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];

            $metadata["fields"] =  ["type"];
        }

        return $metadata;
    }

    protected function registerApprovedItem(Request $request,ItemRequest $itemRequest)
    {
        $data = [
            "name" => $itemRequest->name,
            "estimated_budget" => $itemRequest->estimated_budget,
            "item_unit_id" => $itemRequest->item_unit_id,
            "item_category_id" => $itemRequest->item_category_id,
            "item_classification_id" => $itemRequest->item_classification_id,
            "variant" => $request->variant,
            "code" => $request->code
        ];

        $newItem = Item::create($data);

        return $newItem;
    }

    public function approve(Request $request, ItemRequest $itemRequest)
    {
        $cleanData = [
            "status" => strip_tags($request->status),
            "reason" => strip_tags($request->reason),
            "action_by" => auth()->user()->id
        ];

        $itemRequest->update($cleanData);

        if($request->status === "reject"){
            return response()->json([
                "message" => "Item request is rejected."
            ], Response::HTTP_OK);
        }

        $newItem = $this->registerApprovedItem($request,$itemRequest);

        return response()->json([
            "data" => new ItemResource($newItem),
            "metadata" => [
                "methods" => ["GET, POST, PUT, DELETE"]
            ]
        ], Response::HTTP_CREATED);
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
        $item_id = $request->query('item_id') ?? null;

        if($item_id){
            $item = ItemRequest::find($item_id);

            if(!$item){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => new ItemRequestResource($item),
                "metadata" => $this->getMetadata('get')
            ], Response::HTTP_OK);
        }

        if($page < 0 || $per_page < 0){
            $response = ["message" => "Invalid request."];
            
            if($this->is_development){
                $response = [
                    "message" => "Invalid value of parameters",
                    "metadata" => $this->getMetadata('get')
                ];
            }

            return response()->json([$response], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$page && !$per_page){
            $response = ["message" => "Invalid request."];

            if($this->is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('get')
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle return for selection record
        if($mode === 'selection'){
            if($search !== null){
                $items = ItemRequest::select('id','name','estimated_budget')
                    ->where('name', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => ItemRequestResource::collection($items),
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $items = ItemRequest::where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                    "data" => ItemRequestResource::collection($items),
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $items = ItemRequest::where('name', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($items)  === 0){
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

                $allIds = ItemRequest::where('name', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('items', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $items,
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
            $items = ItemRequest::where('name', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')->limit($per_page)->get();

            // Return the response
            return response()->json([
                'data' => $items,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = ItemRequest::all()->pluck('id')->chunk($per_page);
        $items = ItemRequest::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => ItemRequestResource::collection($items),
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }
    public function store(ItemRequestRequest $request)
    {
        $base_message = "Successfully created items";

        $is_valid_unit_id = ItemUnit::find($request->item_unit_id);
        $is_valid_category_id = ItemCategory::find($request->item_category_id);
        $is_valid_classification_id = ItemClassification::find($request->item_classification_id);

        if(!($is_valid_unit_id && $is_valid_category_id && $is_valid_classification_id)){
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
            "request_by" => auth()->user()->id,
            "reason" => strip_tags($request->reason),
        ];
        
        $new_item = ItemRequest::create($cleanData);
        
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
                    "item_request_id" => $new_item->id,
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
            "data" => new ItemRequestResource($new_item),
            "message" => "Successfully created item record.",
            "metadata" => $metadata
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, ItemRequest $itemRequest): Response
    {   
        $cleanData = $this->cleanItemRequestData($request->all());
        $itemRequest->update($cleanData);
        
        $response = [
            "data" => new ItemRequestResource($itemRequest),
            "message" => "ItemRequest updated successfully.",
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
    
            $items = ItemRequest::whereIn('id', $item_ids)->whereNull('deleted_at')->get();
    
            if ($items->isEmpty()) {
                return response()->json(["message" => "No active records found for the given IDs."], Response::HTTP_NOT_FOUND);
            }
    
            // Only soft-delete records that were actually found
            $found_ids = $items->pluck('id')->toArray();
            ItemRequest::whereIn('id', $found_ids)->update(['deleted_at' => now()]);
    
            return response()->json([
                "message" => "Successfully deleted " . count($found_ids) . " record(s).",
                "deleted_ids" => $found_ids
            ], Response::HTTP_OK); // Changed from NO_CONTENT to OK to allow response body
        }
    
        $items = ItemRequest::where($query)->whereNull('deleted_at')->get();
    
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
