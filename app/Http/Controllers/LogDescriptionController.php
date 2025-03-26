<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\LogDescriptionRequest;
use App\Models\LogDescription;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogDescriptionController extends Controller
{
    private $is_development;

    private $module = 'log-descriptions';

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
    
    protected function cleanLogDescriptionData(array $data): array
    {
        $cleanData = [];

        if (isset($data['title'])) {
            $cleanData['title'] = strip_tags($data['title']);
        }

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
                    env("SERVER_DOMAIN")."/api/".$this->module."?log_description_id=[primary-key]",
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

    public function index(Request $request)
    {
        $page = $request->query('page') > 0? $request->query('page'): 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $log_description_id = $request->query('log_description_id') ?? null;

        if($log_description_id){
            $log_description = LogDescription::find($log_description_id);

            if(!$log_description){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get')
                ]);
            }

            return response()->json([
                'data' => $log_description,
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
                    "metadata" => [
                        "methods" => "[GET]",
                        "modes" => ["pagination", "selection"],
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?log_description_id=[primary-key]",
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
            if($search  !== null){
                $log_descriptions = LogDescription::select('id','title','code')
                    ->where('title', 'like', '%'.$search.'%')
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $log_descriptions,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $log_descriptions = LogDescription::select('id','title','code')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $log_descriptions,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $log_descriptions = LogDescription::where('title', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($log_descriptions)  === 0){
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

                $allIds = LogDescription::where('title', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('log-descriptions', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $log_descriptions,
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

            $log_descriptions = LogDescription::where('title', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')
                ->limit($per_page)
                ->get();

            // Return the response
            return response()->json([
                'data' => $log_descriptions,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = LogDescription::all()->pluck('id')->chunk($per_page);
        $log_descriptions = LogDescription::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $log_descriptions,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(LogDescriptionRequest $request)
    {
        $base_message = "Successfully created item category";

        // Bulk Insert
        if ($request->log_descriptions !== null || $request->log_descriptions > 1) {
            $existing_items = LogDescription::whereIn('title', collect($request->log_descriptions)->pluck('title'))
                ->orWhereIn('code', collect($request->log_descriptions)->pluck('code'))
                ->get(['title', 'code'])->toArray();

            // Convert existing items into a searchable format
            $existing_titles = array_column($existing_items, 'title');
            $existing_codes = array_column($existing_items, 'code');

            foreach ($request->log_descriptions as $item) {
                if (!in_array($item['title'], $existing_titles) && !in_array($item['code'], $existing_codes)) {
                    $cleanData[] = [
                        "title" => strip_tags($item['title']),
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
    
            LogDescription::insert($cleanData);

            $latest_item_log_descriptions = LogDescription::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_item_log_descriptions) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_item_log_descriptions,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_items
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "title" => strip_tags($request->input('title')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $new_item = LogDescription::create([
            "title" => strip_tags($request->title),
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
        $log_description_ids = $request->query('id') ?? null;

        if (!$log_description_ids) {
            $response = ["message" => "ID parameter is required."];

            if ($this->is_development) {
                $response['metadata'] = $this->getMetadata('put');
            }

            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $log_description_ids = is_array($log_description_ids) ? $log_description_ids : [$log_description_ids];

        // For bulk update
        if ($request->has('log_descriptions')) {
            if (count($log_description_ids) !== count($request->input('log_descriptions'))) {
                return response()->json([
                    "message" => "Number of IDs does not match number of log descriptions provided.",
                    "metadata" => $this->getMetadata('put')
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $updated_logs = [];
            $errors = [];

            foreach ($log_description_ids as $index => $id) {
                $log_description = LogDescription::find($id);

                if (!$log_description) {
                    $errors[] = "Log description with ID {$id} not found.";
                    continue;
                }

                $logData = $request->input('log_descriptions')[$index];
                $cleanData = $this->cleanLogDescriptionData($logData);
                
                if (!empty($cleanData)) {
                    $log_description->update($cleanData);
                    $updated_logs[] = $log_description;
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    "data" => $updated_logs,
                    "message" => "Partial update completed with errors.",
                    "errors" => $errors,
                    "metadata" => ["method" => "[PUT]"]
                ], Response::HTTP_MULTI_STATUS);
            }

            return response()->json([
                "data" => $updated_logs,
                "message" => "Successfully updated " . count($updated_logs) . " log descriptions.",
                "metadata" => ["method" => "[PUT]"]
            ], Response::HTTP_OK);
        }

        // Single item update
        if (count($log_description_ids) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no log_descriptions array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $log_description = LogDescription::find($log_description_ids[0]);

        if (!$log_description) {
            return response()->json([
                "message" => "Log description not found."
            ], Response::HTTP_NOT_FOUND);
        }

        $cleanData = $this->cleanLogDescriptionData($request->all());
        
        if (!empty($cleanData)) {
            $log_description->update($cleanData);
        }

        $response = [
            "data" => $log_description,
            "message" => "Log description updated successfully.",
            "metadata" => $this->getMetadata('put')
        ];

        return response()->json($response, Response::HTTP_OK);
    }
    
    public function destroy(Request $request): Response
    {
        $log_description_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;
    
        if (!$log_description_ids && !$query) {
            $response = ["message" => "Invalid request."];
    
            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('delete')
                ];
            }
    
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    
        if ($log_description_ids) {
            $log_description_ids = is_array($log_description_ids) 
                ? $log_description_ids 
                : (str_contains($log_description_ids, ',') 
                    ? explode(',', $log_description_ids) 
                    : [$log_description_ids]
                  );
    
            $log_description_ids = array_filter(array_map('intval', $log_description_ids));
            
            if (empty($log_description_ids)) {
                return response()->json(
                    ["message" => "Invalid log description ID format provided."],
                    Response::HTTP_BAD_REQUEST
                );
            }
    
            $log_descriptions = LogDescription::whereIn('id', $log_description_ids)
                ->whereNull('deleted_at')
                ->get();
    
            if ($log_descriptions->isEmpty()) {
                return response()->json(
                    ["message" => "No active log descriptions found with the provided IDs."],
                    Response::HTTP_NOT_FOUND
                );
            }
    
            $found_ids = $log_descriptions->pluck('id')->toArray();
            
            $deleted_count = LogDescription::whereIn('id', $found_ids)
                ->update(['deleted_at' => now()]);
    
            return response()->json([
                "message" => "Successfully deleted {$deleted_count} log description(s).",
                "deleted_ids" => $found_ids,
                "count" => $deleted_count
            ], Response::HTTP_OK);
        }
    
        $log_descriptions = LogDescription::where($query)
            ->whereNull('deleted_at')
            ->get();

        if ($log_descriptions->count() > 1) {
            return response()->json([
                'data' => $log_descriptions,
                'message' => "Query matches multiple log descriptions. Please specify IDs directly.",
                'suggestion' => "Use ?id parameter for bulk operations or refine your query."
            ], Response::HTTP_CONFLICT);
        }

        $log_description = $log_descriptions->first();

        if (!$log_description) {
            return response()->json(
                ["message" => "No active log description found matching query."],
                Response::HTTP_NOT_FOUND
            );
        }

        $log_description->update(['deleted_at' => now()]);

        return response()->json([
            "message" => "Successfully deleted log description.",
            "deleted_id" => $log_description->id,
            "description" => $log_description->description // Optional: include relevant info
        ], Response::HTTP_OK);
    }
}
