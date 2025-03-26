<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\PurchaseTypeRequest;
use App\Http\Resources\PurchaseTypeDuplicateResource;
use App\Http\Resources\PurchaseTypeResource;
use App\Models\PurchaseType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseTypeController extends Controller
{
    private $is_development;

    private $module = 'purchase-types';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanPurchaseTypeData(array $data): array
    {
        $cleanData = [];
        
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
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if($this->is_development){
                $metadata['urls'] = [
                    env("SERVER_DOMAIN")."/api/".$this->module."?purchase_type_id=[primary-key]",
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
                $metadata['fields'] = ["title", "code", "description"];
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

            $metadata["fields"] =  ["code"];
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
        $page = $request->query('page') > 0? $request->query('page'): 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $purchase_type_id = $request->query('purchase_type_id') ?? null;

        if($purchase_type_id){
            $purchase_type = PurchaseType::find($purchase_type_id);

            if(!$purchase_type){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => $purchase_type,
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
                $purchase_types = PurchaseType::select('id','code','description')
                    ->where('code', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $purchase_types,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $purchase_types = PurchaseType::select('id','code','description')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $purchase_types,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $purchase_types = PurchaseType::where('code', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($purchase_types)  === 0){
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

                $allIds = PurchaseType::where('code', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('purchase-types', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $purchase_types,
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

            $purchase_types = PurchaseType::where('code', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')
                ->limit($per_page)
                ->get();

            // Return the response
            return response()->json([
                'data' => $purchase_types,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = PurchaseType::all()->pluck('id')->chunk($per_page);
        $purchase_types = PurchaseType::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $purchase_types,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(PurchaseTypeRequest $request)
    {
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->purchase_types !== null || $request->purchase_types > 1) {
            $existing_purchase_types = [];
            $existing_items = PurchaseType::whereIn('code', collect($request->purchase_types)->pluck('code'))
                ->get(['code'])->toArray();

            // Convert existing items into a searchable format
            $existing_codes = array_column($existing_items, 'code');

            if(!empty($existing_items)){
                $existing_purchase_types = PurchaseTypeDuplicateResource::collection(PurchaseType::whereIn("code", $existing_codes)->get());
            }

            foreach ($request->purchase_types as $item) {
                if ( !in_array($item['code'], $existing_codes)) {
                    $cleanData[] = [
                        "code" => strip_tags($item['code']),
                        "description" => isset($item['description']) ? strip_tags($item['description']) : null,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_purchase_types,
                    'message' => "Failed to bulk insert all item categories already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            PurchaseType::insert($cleanData);

            $latest_purchase_types = PurchaseType::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_purchase_types) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_purchase_types,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_purchase_types
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];
        
        $new_item = PurchaseType::create($cleanData);

        return response()->json([
            "data" => $new_item,
            "message" => $base_message." record.",
            "metadata" => [
                "methods" => ['GET, POST, PUT, DELET'],
            ]
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request):Response    
    {
        $purchase_types = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$purchase_types) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $purchase_types = is_array($purchase_types) ? $purchase_types : [$purchase_types];
        
        // For bulk update - validate purchase_types array matches IDs count
        if ($request->has('purchase_types')) {
            if (count($purchase_types) !== count($request->input('purchase_types'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of purchase_types provided.",
                    "metadata" => $this->getMetadata("put"),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $updated_items = [];
            $errors = [];
            
            foreach ($purchase_types as $index => $id) {
                $purchase_type = PurchaseType::find($id);
                
                if (!$purchase_type) {
                    $errors[] = "PurchaseType with ID {$id} not found.";
                    continue;
                }
                
                $purchase_typeData = $request->input('purchase_types')[$index];
                $cleanData = $this->cleanPurchaseTypeData($purchase_typeData);
                
                $purchase_type->update($cleanData);
                $updated_purchase_types[] = $purchase_type;
            }
            
            if (!empty($errors)) {
                return response()->json([
                    "data" => PurchaseTypeResource::collection($updated_purchase_types),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [                    
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }
            
            return response()->json([
                "data" => PurchaseTypeResource::collection($updated_purchase_types),
                "message" => "Successfully updated ".count($updated_purchase_types)." items.",
                "metadata" => $this->getMetadata('put')
            ], Response::HTTP_OK);
        }
        
        // Single item update
        if (count($purchase_types) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no purchase type array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $item = PurchaseType::find($purchase_types[0]);
        
        if (!$item) {
            return response()->json([
                "message" => "PurchaseType not found."
            ], Response::HTTP_NOT_FOUND);
        }
        
        $cleanData = $this->cleanPurchaseTypeData($request->all());
        $item->update($cleanData);
        
        $response = [
            "data" => new PurchaseTypeResource($item),
            "message" => "Purchase Type updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];
        
        if ($this->is_development) {
            $response['metadata'] = $this->getMetadata('put');
        }
        
        return response()->json($response, Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $purchase_type_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$purchase_type_ids && !$query) {
            $response = ["message" => "Invalid request. No parameters provided."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found for deletion.",
                    "metadata" => $this->getMetadata('delete'),
                    "hint" => "Provide either 'id' or 'query' parameter"
                ];
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($purchase_type_ids) {
            // Handle all ID formats: single, comma-separated, and array-style
            $purchase_type_ids = is_array($purchase_type_ids) 
                ? $purchase_type_ids 
                : (str_contains($purchase_type_ids, ',') 
                    ? explode(',', $purchase_type_ids) 
                    : [$purchase_type_ids]);

            // Validate and sanitize IDs
            $valid_ids = [];
            foreach ($purchase_type_ids as $id) {
                if (is_numeric($id) && $id > 0) {
                    $valid_ids[] = (int)$id;
                }
            }

            if (empty($valid_ids)) {
                return response()->json(
                    ["message" => "Invalid purchase type ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Get only active purchase types that exist
            $purchase_types = PurchaseType::whereIn('id', $valid_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($purchase_types->isEmpty()) {
                return response()->json(
                    ["message" => "No active purchase types found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Get the IDs that were actually found
            $found_ids = $purchase_types->pluck('id')->toArray();
            
            // Perform soft delete and get count
            $deleted_count = PurchaseType::whereIn('id', $found_ids)
                ->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted {$deleted_count} purchase type(s).",
                "deleted_ids" => $found_ids,
                "count" => $deleted_count,
                "remaining_active" => PurchaseType::whereNull('deleted_at')->count()
            ], Response::HTTP_OK);
        }
        
        $purchase_types = PurchaseType::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($purchase_types->count() > 1) {
            return response()->json([
                'data' => $purchase_types,
                'message' => "Query matches multiple purchase types. Please be more specific.",
                'suggestion' => [
                    'use_ids' => "Use ?id parameter for precise deletion",
                    'add_criteria' => "Add more query parameters to narrow down results"
                ]
            ], Response::HTTP_CONFLICT);
        }

        $purchase_type = $purchase_types->first();

        if (!$purchase_type) {
            return response()->json(
                ["message" => "No active purchase type found matching your criteria."],
                Response::HTTP_NOT_FOUND
            );
        }

        $purchase_type->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted purchase type.",
            "deleted_id" => $purchase_type->id,
            "type_name" => $purchase_type->name,
            "remaining_active" => PurchaseType::whereNull('deleted_at')->count()
        ], Response::HTTP_OK);
    }
}
