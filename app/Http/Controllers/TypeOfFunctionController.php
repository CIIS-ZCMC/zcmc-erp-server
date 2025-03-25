<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\TypeOfFunctionRequest;
use App\Http\Resources\TypeOfFunctionDuplicateResource;
use App\Http\Resources\TypeOfFunctionResource;
use App\Models\TypeOfFunction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeOfFunctionController extends Controller
{
    private $is_development;

    private $module = 'type-of-functions';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanTypeOfFunctionData(array $data): array
    {
        $cleanData = [];
        
        if (isset($data['type'])) {
            $cleanData['type'] = strip_tags($data['type']);
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
                    env("SERVER_DOMAIN")."/api/".$this->module."?type_of_function_id=[primary-key]",
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
        $type_of_function_id = $request->query('type_of_function_id') ?? null;

        if($type_of_function_id){
            $type_of_function = TypeOfFunction::find($type_of_function_id);

            if(!$type_of_function){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => $type_of_function,
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
                $type_of_functions = TypeOfFunction::select('id','type')
                    ->where('type', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $type_of_functions,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $type_of_functions = TypeOfFunction::select('id','type')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $type_of_functions,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $type_of_functions = TypeOfFunction::where('type', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($type_of_functions)  === 0){
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

                $allIds = TypeOfFunction::where('type', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('type-of-functions', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $type_of_functions,
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

            $type_of_functions = TypeOfFunction::where('type', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')
                ->limit($per_page)
                ->get();

            // Return the response
            return response()->json([
                'data' => $type_of_functions,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = TypeOfFunction::all()->pluck('id')->chunk($per_page);
        $type_of_functions = TypeOfFunction::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $type_of_functions,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(TypeOfFunctionRequest $request)
    {
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->type_of_functions !== null || $request->type_of_functions > 1) {
            $existing_type_of_functions = [];
            $existing_items = TypeOfFunction::whereIn('type', collect($request->type_of_functions)->pluck('type'))
                ->get(['type'])->toArray();

            // Convert existing items into a searchable format
            $existing_types = array_column($existing_items, 'type');

            if(!empty($existing_items)){
                $existing_type_of_functions = TypeOfFunctionDuplicateResource::collection(TypeOfFunction::whereIn("type", $existing_types)->get());
            }

            foreach ($request->type_of_functions as $item) {
                if ( !in_array($item['type'], $existing_types)) {
                    $cleanData[] = [
                        "type" => strip_tags($item['type']),
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_type_of_functions,
                    'message' => "Failed to bulk insert all item categories already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            TypeOfFunction::insert($cleanData);

            $latest_type_of_functions = TypeOfFunction::orderBy('id', direction: 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_type_of_functions) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_type_of_functions,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_type_of_functions
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "type" => strip_tags($request->input('type'))
        ];
        
        $new_item = TypeOfFunction::create($cleanData);

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
        $type_of_functions = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$type_of_functions) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $type_of_functions = is_array($type_of_functions) ? $type_of_functions : [$type_of_functions];
        
        // For bulk update - validate items array matches IDs count
        if ($request->has('type_of_functions')) {
            if (count($type_of_functions) !== count($request->input('type_of_functions'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of type of functions provided.",
                    "metadata" => $this->getMetadata('put')
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $updated_type_of_functions = [];
            $errors = [];
            
            foreach ($type_of_functions as $index => $id) {
                $type_of_function = TypeOfFunction::find($id);
                
                if (!$type_of_function) {
                    $errors[] = "TypeOfFunction with ID {$id} not found.";
                    continue;
                }
                
                $type_of_functionData = $request->input('type_of_functions')[$index];
                $cleanData = $this->cleanTypeOfFunctionData($type_of_functionData);
                
                $type_of_function->update($cleanData);
                $updated_type_of_functions[] = $type_of_function;
            }
            
            if (!empty($errors)) {
                return response()->json([
                    "data" => TypeOfFunctionResource::collection($updated_type_of_functions),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [                    
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }
            
            return response()->json([
                "data" => TypeOfFunctionResource::collection($updated_type_of_functions),
                "message" => "Successfully updated ".count($updated_type_of_functions)." type of functions.",
                "metadata" => $this->getMetadata('put')
            ], Response::HTTP_OK);
        }
        
        // Single type_of_function update
        if (count($type_of_functions) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no type of functions array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $type_of_function = TypeOfFunction::find($type_of_functions[0]);
        
        if (!$type_of_function) {
            return response()->json([
                "message" => "Type of function not found."
            ], Response::HTTP_NOT_FOUND);
        }
        
        $cleanData = $this->cleanTypeOfFunctionData($request->all());
        $type_of_function->update($cleanData);
        
        $response = [
            "data" => new TypeOfFunctionResource($type_of_function),
            "message" => "Type of Function updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];
        
        if ($this->is_development) {
            $response['metadata'] = [
                "methods" => "[PUT]",
                "required_fields" => ["type"]
            ];
        }
        
        return response()->json($response, Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $type_of_function_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;
    
        if (!$type_of_function_ids && !$query) {
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
    
        if ($type_of_function_ids) {
            // Handle all ID formats: single, comma-separated, and array-style
            $type_of_function_ids = is_array($type_of_function_ids) 
                ? $type_of_function_ids 
                : (str_contains($type_of_function_ids, ',') 
                    ? explode(',', $type_of_function_ids) 
                    : [$type_of_function_ids]);
    
            // Validate and sanitize IDs
            $valid_ids = array_filter(array_map(function($id) {
                return is_numeric($id) && $id > 0 ? (int)$id : null;
            }, $type_of_function_ids));
    
            if (empty($valid_ids)) {
                return response()->json(
                    ["message" => "Invalid function type ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            // Get only active function types that exist
            $type_of_functions = TypeOfFunction::whereIn('id', $valid_ids)
                ->whereNull('deleted_at')
                ->get();
    
            if ($type_of_functions->isEmpty()) {
                return response()->json(
                    ["message" => "No active function types found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            // Perform soft delete
            $deleted_count = TypeOfFunction::whereIn('id', $valid_ids)
                ->update(['deleted_at' => now()]);
    
            return response()->json([
                "message" => "Successfully deleted {$deleted_count} function type(s).",
                "deleted_ids" => $valid_ids,
                "count" => $deleted_count
            ], Response::HTTP_OK);
        }
        
        $type_of_functions = TypeOfFunction::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($type_of_functions->count() > 1) {
            return response()->json([
                'data' => TypeOfFunctionResource::collection($type_of_functions),
                'message' => "Query matches multiple function types.",
                'suggestion' => "Use ID parameter for precise deletion or add more query criteria"
            ], Response::HTTP_CONFLICT);
        }

        $type_of_function = $type_of_functions->first();

        if (!$type_of_function) {
            return response()->json(
                ["message" => "No active function type found matching your criteria."],
                Response::HTTP_NOT_FOUND
            );
        }

        $type_of_function->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted function type.",
            "deleted_id" => $type_of_function->id,
            "function_name" => $type_of_function->name
        ], Response::HTTP_OK);
    }
}
