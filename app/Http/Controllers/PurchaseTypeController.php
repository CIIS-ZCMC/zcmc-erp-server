<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\PurchaseTypeRequest;
use App\Http\Resources\PurchaseTypeDuplicateResource;
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
                    "metadata" => [
                        "methods" => "[GET, POST, PUT, DELETE]",
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?purchase_type_id=[primary-key]",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                        ]
                    ]
                ]);
            }

            return response()->json([
                'data' => $purchase_type,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "urls" => [
                        env("SERVER_DOMAIN")."/api/".$this->module."?purchase_type_id=[primary-key]",
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
                            env("SERVER_DOMAIN")."/api/".$this->module."?purchase_type_id=[primary-key]",
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
                            env("SERVER_DOMAIN")."/api/".$this->module."?purchase_type_id=[primary-key]",
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
        $purchase_type_id = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if(!$purchase_type_id && !$query){
            $response = ["message" => "Invalid request."];

            if($this->is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                        "formats" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                            env("SERVER_DOMAIN")."/api/".$this->module."query[target_field]=value"
                        ],
                        "fields" => ["code"]
                    ]
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $success_indicator = null;

        if($purchase_type_id){
            $success_indicator = PurchaseType::find($purchase_type_id);    
        }

        if(!$purchase_type_id && $query){
            $purchase_types = PurchaseType::where($query)->get();
            
            // Check result is has many records
            if(count($purchase_types) > 1){
                return response()->json([
                    'data' => $purchase_types,
                    'message' => "Request has multiple record."
                ], Response::HTTP_CONFLICT);
            }

            $success_indicator = $purchase_types->first();
        }
        
        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];

        $success_indicator->update($cleanData);

        $metadata = [
            "methods" => "[GET, PUT, DELETE]",
        ];

        if($this->is_development){
            $metadata["formats"] = [
                env("SERVER_DOMAIN")."/api/".$this->module."?id=1",
                env("SERVER_DOMAIN")."/api/".$this->module."query[target_field]=value"
            ];
            
            $metadata['fields'] = ["code"];
        }

        return response()->json([
            "data" => $success_indicator,
            "metadata" => $metadata
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $purchase_type_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$purchase_type_ids && !$query) {
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


        if ($purchase_type_ids) {
            $purchase_type_ids = is_array($purchase_type_ids) ? $purchase_type_ids : explode(',', $purchase_type_ids);
            $purchase_types = PurchaseType::whereIn('id', $purchase_type_ids)->where('deleted_at', NULL)->get();

            if ($purchase_types->isEmpty()) {
                return response()->json(["message" => "No records found."], Response::HTTP_NOT_FOUND);
            }

            PurchaseType::whereIn('id', $purchase_type_ids)->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted " . count($purchase_types) . " records."
            ], Response::HTTP_NO_CONTENT);
        }

        if ($query) {
            $purchase_types = PurchaseType::where($query)->get();

            if ($purchase_types->count() > 1) {
                return response()->json([
                    'data' => $purchase_types,
                    'message' => "Request has multiple records."
                ], Response::HTTP_CONFLICT);
            }

            $purchase_type = $purchase_types->first();

            if (!$purchase_type) {
                return response()->json(["message" => "No record found."], Response::HTTP_NOT_FOUND);
            }

            $purchase_type->update(['deleted_at' => now()]);
        }

        return response()->json(["message" => "Successfully deleted record."], Response::HTTP_NO_CONTENT);
    }
}
