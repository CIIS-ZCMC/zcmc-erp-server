<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ObjectiveRequest;
use App\Http\Resources\ObjectiveDuplicateResource;
use App\Http\Resources\ObjectiveResource;
use App\Models\Objective;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectiveController extends Controller
{
    private $is_development;

    private $module = 'objectives';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanObjectivesData(array $data): array
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
                    env("SERVER_DOMAIN")."/api/".$this->module."?objective_id=[primary-key]",
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
        $objective_id = $request->query('objective_id') ?? null;

        if($objective_id){
            $purchase_type = Objective::find($objective_id);

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
                $objectives = Objective::select('id','code','description')
                    ->where('code', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $objectives,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $objectives = Objective::select('id','code','description')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $objectives,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $objectives = Objective::where('code', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($objectives)  === 0){
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

                $allIds = Objective::where('code', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('objectives', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $objectives,
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

            $objectives = Objective::where('code', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')->limit($per_page)->get();

            // Return the response
            return response()->json([
                'data' => $objectives,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = Objective::all()->pluck('id')->chunk($per_page);
        $objectives = Objective::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $objectives,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(ObjectiveRequest $request)
    {
        $base_message = "Successfully created objectives";

        // Bulk Insert
        if ($request->objectives !== null || $request->objectives > 1) {
            $existing_objectives = [];
            $existing_items = Objective::whereIn('code', collect($request->objectives)->pluck('code'))
                ->get(['code'])->toArray();

            // Convert existing items into a searchable format
            $existing_codes = array_column($existing_items, 'code');

            if(!empty($existing_items)){
                $existing_objectives = ObjectiveDuplicateResource::collection(Objective::whereIn("code", $existing_codes)->get());
            }

            foreach ($request->objectives as $item) {
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
                    'data' => $existing_objectives,
                    'message' => "Failed to bulk insert all objectives already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            Objective::insert($cleanData);

            $latest_objectives = Objective::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_objectives) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_objectives,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_objectives
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];
        
        $new_item = Objective::create($cleanData);

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
        $objectives = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$objectives) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $objectives = is_array($objectives) ? $objectives : [$objectives];
        
        // For bulk update - validate items array matches IDs count
        if ($request->has('items')) {
            if (count($objectives) !== count($request->input('items'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of objectives provided.",
                    "metadata" => $this->getMetadata("put"),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $updated_items = [];
            $errors = [];
            
            foreach ($objectives as $index => $id) {
                $item = Objective::find($id);
                
                if (!$item) {
                    $errors[] = "Objectives with ID {$id} not found.";
                    continue;
                }
                
                $itemData = $request->input('items')[$index];
                $cleanData = $this->cleanObjectivesData($itemData);
                
                $item->update($cleanData);
                $updated_items[] = $item;
            }
            
            if (!empty($errors)) {
                return response()->json([
                    "data" => ObjectiveResource::collection($updated_items),
                    "message" => "Partial update completed with errors.",
                    "metadata" => [                    
                        "method" => "[PUT]",
                        "errors" => $errors,
                    ]
                ], Response::HTTP_MULTI_STATUS);
            }
            
            return response()->json([
                "data" => ObjectiveResource::collection($updated_items),
                "message" => "Successfully updated ".count($updated_items)." items.",
                "metadata" => $this->getMetadata('put')
            ], Response::HTTP_OK);
        }
        
        // Single item update
        if (count($objectives) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no items array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $item = Objective::find($objectives[0]);
        
        if (!$item) {
            return response()->json([
                "message" => "Objectives not found."
            ], Response::HTTP_NOT_FOUND);
        }
        
        $cleanData = $this->cleanObjectivesData($request->all());
        $item->update($cleanData);
        
        $response = [
            "data" => new ObjectiveResource($item),
            "message" => "Objective updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $objective_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;
    
        if (!$objective_ids && !$query) {
            $response = ["message" => "Invalid request."];
    
            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('delete')
                ];
            }
    
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if ($objective_ids) {
            $objective_ids = is_array($objective_ids) 
                ? $objective_ids 
                : (str_contains($objective_ids, ',') 
                    ? explode(',', $objective_ids) 
                    : [$objective_ids]
                  );
    
            $objective_ids = array_filter(array_map('intval', $objective_ids));
            
            if (empty($objective_ids)) {
                return response()->json(
                    ["message" => "Invalid objective ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $objectives = Objective::whereIn('id', $objective_ids)
                ->whereNull('deleted_at')
                ->get();
    
            if ($objectives->isEmpty()) {
                return response()->json(
                    ["message" => "No active objectives found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            $found_ids = $objectives->pluck('id')->toArray();
            
            $deletedCount = Objective::whereIn('id', $found_ids)
                ->update(['deleted_at' => now()]);
    
            return response()->json([
                "message" => "Successfully deleted {$deletedCount} objective(s).",
                "deleted_ids" => $found_ids,
                "count" => $deletedCount
            ], Response::HTTP_OK);
        }
    
        $objectives = Objective::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($objectives->count() > 1) {
            return response()->json([
                'data' => $objectives,
                'message' => "Query matches multiple objectives. Please specify IDs directly.",
                'suggestion' => "Use ?id parameter for bulk operations or add more specific query criteria."
            ], Response::HTTP_CONFLICT);
        }

        $objective = $objectives->first();

        if (!$objective) {
            return response()->json(
                ["message" => "No active objective found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $objective->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted objective.",
            "deleted_id" => $objective->id,
            "objective_name" => $objective->name // Include relevant objective info
        ], Response::HTTP_OK);
    }
}
