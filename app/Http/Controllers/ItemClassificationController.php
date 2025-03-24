<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemClassificationRequest;
use App\Http\Resources\ItemClassificationDuplicateResource;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemClassificationController extends Controller
{
    private $is_development;

    private $module = 'item_classifications';

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
        $item_classification_id = $request->query('item_classification_id') ?? null;

        if($item_classification_id){
            $item_classification = ItemClassification::find($item_classification_id);

            if(!$item_classification){
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => [
                        "methods" => "[GET, POST, PUT, DELETE]",
                        "urls" => [
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_classification_id=[primary-key]",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                            env("SERVER_DOMAIN")."/api/".$this->module."?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                        ]
                    ]
                ]);
            }

            return response()->json([
                'data' => $item_classification,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "urls" => [
                        env("SERVER_DOMAIN")."/api/".$this->module."?item_classification_id=[primary-key]",
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
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_classification_id=[primary-key]",
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
                            env("SERVER_DOMAIN")."/api/".$this->module."?item_classification_id=[primary-key]",
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
                $item_classifications = ItemClassification::select('id','code','description')
                    ->where('code', 'like', "%".$search."%")
                    ->where("deleted_at", NULL)->get();
    
                $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];
    
                if($this->is_development){
                    $metadata['content'] = "This type of response is for selection component.";
                    $metadata['mode'] = "selection";
                }
                
                return response()->json([
                    "data" => $item_classifications,
                    "metadata" => $metadata,
                ], Response::HTTP_OK);
            }

            $item_classifications = ItemClassification::select('id','code','description')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if($this->is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $item_classifications,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        

        if($search !== null){
            if($last_id === 0 || $page_item != null){
                $item_classifications = ItemClassification::where('code', 'like', '%'.$search.'%')
                    ->where('id','>', $last_id)
                    ->orderBy('id')
                    ->limit($per_page)
                    ->get();

                if(count($item_classifications)  === 0){
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

                $allIds = ItemClassification::where('code', 'like', '%'.$search.'%')
                    ->orderBy('id')
                    ->pluck('id');

                $chunks = $allIds->chunk($per_page);
                
                $pagination_helper = new PaginationHelper('item_classifications', $page, $per_page, 0);
                $pagination = $pagination_helper->createSearchPagination( $page_item, $chunks, $search, $per_page, $last_initial_id);
                $pagination = $pagination_helper->prevAppendSearchPagination($pagination, $search, $per_page, $last_initial_id, $last_id);
                
                /**
                 * Save the metadata in database unique per module and user to ensure reuse of metadata
                 */

                return response()->json([
                    'data' => $item_classifications,
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

            $item_classifications = ItemClassification::where('code', 'like', '%'.$search.'%')
                ->where('id','>', $last_id)
                ->orderBy('id')->limit($per_page)->get();

            // Return the response
            return response()->json([
                'data' => $item_classifications,
                'metadata' => []
            ], Response::HTTP_OK);
        }
        
        $total_page = ItemClassification::all()->pluck('id')->chunk($per_page);
        $item_classifications = ItemClassification::where('deleted_at', NULL)->limit($per_page)->offset(($page - 1) * $per_page)->get();
        $total_page = ceil(count($total_page));
        
        $pagination_helper = new PaginationHelper(  $this->module,$page, $per_page, $total_page > 10 ? 10: $total_page);

        return response()->json([
            "data" => $item_classifications,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => $pagination_helper->create(),
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(ItemClassificationRequest $request)
    {
        $base_message = "Successfully created item_classifications";

        // Bulk Insert
        if ($request->item_classifications !== null || $request->item_classifications > 1) {
            $existing_item_classifications = [];
            $existing_items = ItemClassification::whereIn('name', collect($request->item_units)->pluck('name'))
                ->whereIn('code', collect($request->item_classifications)->pluck('code'))->get(['code'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');
            $existing_codes = array_column($existing_items, 'code');

            if(!empty($existing_items)){
                $existing_item_classifications = ItemClassificationDuplicateResource::collection(ItemClassification::whereIn("code", $existing_codes)->get());
            }

            foreach ($request->item_classifications as $item) {
                $is_valid_category_id = ItemCategory::find($item['item_category_id']);

                if(!$is_valid_category_id){
                    return response()->json([
                        "message" => "Found invalid Cateogry ID.",
                        "metadata" => [
                            "methods" => "[GET, PUT, DELETE]",
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }

                if (!in_array($item['name'], $existing_names) &&  !in_array($item['code'], $existing_codes)) {
                    $cleanData[] = [
                        "name" => strip_tags($item['name']),
                        "code" => strip_tags($item['code']),
                        "description" => isset($item['description']) ? strip_tags($item['description']) : null,
                        "item_category_id" => strip_tags($item['item_category_id']),
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_item_classifications,
                    'message' => "Failed to bulk insert all item_classifications already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            ItemClassification::insert($cleanData);

            $latest_item_classifications = ItemClassification::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_item_classifications) > 1? $base_message."s record": $base_message." record.";

            return response()->json([
                "data" => $latest_item_classifications,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT ,DELETE]",
                    "duplicate_items" => $existing_item_classifications
                ]
            ], Response::HTTP_CREATED);
        }

        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
            "item_category_id" => strip_tags($request->input('item_category_id')),
        ];
        
        $new_item = ItemClassification::create($cleanData);

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
        $item_classification_id = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if(!$item_classification_id && !$query){
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
        
        $item_classification = null;

        if($item_classification_id){
            $item_classification = ItemClassification::find($item_classification_id);    
        }

        if(!$item_classification_id && $query){
            $item_classifications = ItemClassification::where($query)->get();
            
            // Check result is has many records
            if(count($item_classifications) > 1){
                return response()->json([
                    'data' => $item_classifications,
                    'message' => "Request has multiple record."
                ], Response::HTTP_CONFLICT);
            }

            $item_classification = $item_classifications->first();
        }
        
        $cleanData = [
            "name" => strip_tags($request->input('name')),
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
            "category_id" => strip_tags($request->input('category_id')),
        ];

        $item_classification->update($cleanData);

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
            "data" => $item_classification,
            "metadata" => $metadata
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $item_classification_ids = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if (!$item_classification_ids && !$query) {
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


        if ($item_classification_ids) {
            $item_classification_ids = is_array($item_classification_ids) ? $item_classification_ids : explode(',', $item_classification_ids);
            $item_classifications = ItemClassification::whereIn('id', $item_classification_ids)->where('deleted_at', NULL)->get();

            if ($item_classifications->isEmpty()) {
                return response()->json(["message" => "No records found."], Response::HTTP_NOT_FOUND);
            }

            ItemClassification::whereIn('id', $item_classification_ids)->update(['deleted_at' => now()]);

            return response()->json([
                "message" => "Successfully deleted " . count($item_classifications) . " records."
            ], Response::HTTP_NO_CONTENT);
        }

        if ($query) {
            $item_classifications = ItemClassification::where($query)->get();

            if ($item_classifications->count() > 1) {
                return response()->json([
                    'data' => $item_classifications,
                    'message' => "Request has multiple records."
                ], Response::HTTP_CONFLICT);
            }

            $item_classification = $item_classifications->first();

            if (!$item_classification) {
                return response()->json(["message" => "No record found."], Response::HTTP_NOT_FOUND);
            }

            $item_classification->update(['deleted_at' => now()]);
        }

        return response()->json(["message" => "Successfully deleted record."], Response::HTTP_NO_CONTENT);
    }
}
