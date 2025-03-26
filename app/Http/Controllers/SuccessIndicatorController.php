<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\SuccessIndicatorRequest;
use App\Http\Resources\SuccessIndicatorDuplicateResource;
use App\Http\Resources\SuccessIndicatorResource;
use App\Models\SuccessIndicator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuccessIndicatorController extends Controller
{
    private $is_development;

    private $module = 'success-indicators';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanSuccessIndicatorData(array $data): array
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
                    env("SERVER_DOMAIN")."/api/".$this->module."?success_indicator_id=[primary-key]",
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
        $success_indicator_id = $request->query('success_indicator_id') ?? null;

        if($success_indicator_id){
            $item_category = SuccessIndicator::find($success_indicator_id);

            if(!$item_category){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => $item_category,
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
                $success_indicators = SuccessIndicator::select('id','code','description')
                    ->where('code', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $success_indicators,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $success_indicators = SuccessIndicator::select('id','code','description')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $success_indicators,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $success_indicators = SuccessIndicator::where('code', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($success_indicators)  === 0){
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

                $allIds = SuccessIndicator::where('code', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('success-indicators', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $success_indicators,
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

            $success_indicators = SuccessIndicator::where('code', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')
                ->limit($per_page)
                ->get();

            // Return the response
            return response()->json([
                'data' => $success_indicators,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = SuccessIndicator::all()->pluck('id')->chunk($per_page);
        $success_indicators = SuccessIndicator::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $success_indicators,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(SuccessIndicatorRequest $request)
    {
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->success_indicators !== null || $request->success_indicators > 1) {
            $existing_success_indicators = [];
            $existing_items = SuccessIndicator::whereIn('code', collect($request->success_indicators)->pluck('code'))
                ->get(['code'])->toArray();

            // Convert existing items into a searchable format
            $existing_codes = array_column($existing_items, 'code');

            if(!empty($existing_items)){
                $existing_success_indicators = SuccessIndicatorDuplicateResource::collection(SuccessIndicator::whereIn("code", $existing_codes)->get());
            }

            foreach ($request->success_indicators as $item) {
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
                    'data' => $existing_success_indicators,
                    'message' => "Failed to bulk insert all item categories already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            SuccessIndicator::insert($cleanData);

            $latest_success_indicators = SuccessIndicator::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_success_indicators) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_success_indicators,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_success_indicators
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];
        
        $new_item = SuccessIndicator::create($cleanData);

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
        $success_indicators = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$success_indicators) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $success_indicators = is_array($success_indicators) ? $success_indicators : [$success_indicators];
        
        // For bulk update - validate items array matches IDs count
        if ($request->has('success_indicators')) {
            if (count($success_indicators) !== count($request->input('success_indicators'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of success indicators provided.",
                    "metadata" => $this->getMetadata('put')
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $updated_success_indicators = [];
            $errors = [];
            
            foreach ($success_indicators as $index => $id) {
                $success_indicator = SuccessIndicator::find($id);
                
                if (!$success_indicator) {
                    $errors[] = "SuccessIndicator with ID {$id} not found.";
                    continue;
                }
                
                $success_indicatorData = $request->input('success_indicators')[$index];
                $cleanData = $this->cleanSuccessIndicatorData($success_indicatorData);
                
                $success_indicator->update($cleanData);
                $updated_success_indicators[] = $success_indicator;
            }
            
            if (!empty($errors)) {
                return response()->json([
                    "data" => SuccessIndicatorResource::collection($updated_success_indicators),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [                    
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }
            
            return response()->json([
                "data" => SuccessIndicatorResource::collection($updated_success_indicators),
                "message" => "Successfully updated ".count($updated_success_indicators)." success indicators.",
                "metadata" => [              
                    "method" => "[GET, POST, PUT, DELETE]"
                ]
            ], Response::HTTP_OK);
        }
        
        // Single item update
        if (count($success_indicators) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no success_indicators array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $item = SuccessIndicator::find($success_indicators[0]);
        
        if (!$item) {
            return response()->json([
                "message" => "SuccessIndicator not found."
            ], Response::HTTP_NOT_FOUND);
        }
        
        $cleanData = $this->cleanSuccessIndicatorData($request->all());
        $item->update($cleanData);
        
        $response = [
            "data" => new SuccessIndicatorResource($item),
            "message" => "SuccessIndicator updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];
        
        return response()->json($response, Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $success_indicator_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$success_indicator_ids && !$query) {
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

        if ($success_indicator_ids) {
            // Handle all ID formats: single, comma-separated, and array-style
            $success_indicator_ids = is_array($success_indicator_ids) 
                ? $success_indicator_ids 
                : (str_contains($success_indicator_ids, ',') 
                    ? explode(',', $success_indicator_ids) 
                    : [$success_indicator_ids]);

            // Validate and sanitize IDs
            $valid_ids = array_filter(array_map(function($id) {
                return is_numeric($id) && $id > 0 ? (int)$id : null;
            }, $success_indicator_ids));

            if (empty($valid_ids)) {
                return response()->json(
                    ["message" => "Invalid success indicator ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Get only active success indicators that exist
            $success_indicators = SuccessIndicator::whereIn('id', $valid_ids)
                ->whereNull('deleted_at')
                ->get();

            if ($success_indicators->isEmpty()) {
                return response()->json(
                    ["message" => "No active success indicators found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Perform soft delete
            $deleted_count = SuccessIndicator::whereIn('id', $valid_ids)
                ->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted {$deleted_count} success indicator(s).",
                "deleted_ids" => $valid_ids,
                "count" => $deleted_count
            ], Response::HTTP_OK);
        }

        $success_indicators = SuccessIndicator::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($success_indicators->count() > 1) {
            return response()->json([
                'data' => $success_indicators,
                'message' => "Query matches multiple success indicators.",
                'suggestion' => "Use ID parameter for precise deletion or add more query criteria"
            ], Response::HTTP_CONFLICT);
        }

        $success_indicator = $success_indicators->first();

        if (!$success_indicator) {
            return response()->json(
                ["message" => "No active success indicator found matching your criteria."],
                Response::HTTP_NOT_FOUND
            );
        }

        $success_indicator->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted success indicator.",
            "deleted_id" => $success_indicator->id,
            "indicator_name" => $success_indicator->name
        ], Response::HTTP_OK);
    }
}
