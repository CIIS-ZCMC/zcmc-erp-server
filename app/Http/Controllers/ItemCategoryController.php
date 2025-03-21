<?php

namespace App\Http\Controllers;

use App\Helpers\PaginationHelper;
use App\Http\Requests\ItemCategoryRequest;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemCategoryController extends Controller
{
    private $is_development = env("APP_DEBUG") ?? true;
    private $module = 'item-categories';

    public function import(Request $request)
    {
        return response()->json([
            'message' => "Succesfully imported record"
        ], Response::HTTP_OK);
    }

    public function index(Request $request)
    {
        $page = $request->query('page');
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';

        if(!$page && !$per_page){
            $response = ["message" => "Invalid request."];

            if(self::$is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => [
                        "methods" => "[GET]",
                        "formats" => [
                            env("SERVER_DOMAIN")."/api/".self::$module."?page={currentPage}&per_page={number_of_record_to_return}",
                            env("SERVER_DOMAIN")."/api/".self::$module."?page={currentPage}&per_page={number_of_record_to_return}&mode=selection"
                        ],
                    ]
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Handle return for selection record
        if($mode === 'selection'){
            $item_units = ItemCategory::select('id','name','code')->where("deleted_at", NULL)->get();

            $metadata = ["methods" => '[GET, POST, PUT, DELETE]'];

            if(self::$is_development){
                $metadata['content'] = "This type of response is for selection component.";
                $metadata['mode'] = "selection";
            }
            
            return response()->json([
                "data" => $item_units,
                "metadata" => $metadata,
            ], Response::HTTP_OK);
        }
        
        $total_page = ItemCategory::all()->pluck('id')->chunk($per_page);
        $item_categories = ItemCategory::limit($per_page)->offset($page * $per_page)->get();
        
        $pagination_helper = new PaginationHelper( module: self::$module,page: $page, per_page: $per_page, total_page: $total_page > 10 ? 10: $total_page);

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
        $new_item = ItemCategory::create([
            "name" => strip_tags($request->name),
            "code" => strip_tags($request->code),
            "description" => strip_tags($request->description),
        ]);

        return response()->json([
            "data" => $new_item,
            "metadata" => [
                "methods" => ['GET, POST, PUT, DELET'],
            ]
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request):Response    
    {
        $item_category_id = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if(!$item_category_id && !$query){
            $response = ["message" => "Invalid request."];

            if(self::$is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                        "formats" => [
                            env("SERVER_DOMAIN")."/api/".self::$module."?id=1",
                            env("SERVER_DOMAIN")."/api/".self::$module."query[target_field]=value"
                        ],
                        "fields" => ["name", "code"]
                    ]
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($item_category_id){
            $item_category = ItemCategory::find($item_category_id);    
        }

        if(!$item_category_id && $query){
            $item_categorys = ItemCategory::where($query)->get();
            
            // Check result is has many records
            if(count($item_category) > 1){
                return response()->json([
                    'data' => $item_categorys,
                    'message' => "Request has multiple record."
                ], Response::HTTP_CONFLICT);
            }

            $item_category = $item_categorys->first();
        }

        $item_category->update([
            "name" => strip_tags($request->input("name")),
            "code" => strip_tags($request->code),
            "description" => $request->description
        ]);

        $metadata = [
            "methods" => "[GET, PUT, DELETE]",
        ];

        if(self::$is_development){
            $metadata["formats"] = [
                env("SERVER_DOMAIN")."/api/".self::$module."?id=1",
                env("SERVER_DOMAIN")."/api/".self::$module."query[target_field]=value"
            ];
            
            $metadata['fields'] = ["name", "code"];
        }

        return response()->json([
            "data" => $item_category,
            "metadata" => $metadata
        ], Response::HTTP_OK);
    }

    public function destroy(Request $request): Response
    {
        $item_category_id = $request->query('id') ?? null;
        $query = $request->query('query') ?? null;

        if(!$item_category_id && !$query){

            $response = ["message" => "Invalid request."];

            if(self::$is_development){
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                        "formats" => [
                            env("SERVER_DOMAIN")."/api/".self::$module."?id=1",
                            env("SERVER_DOMAIN")."/api/".self::$module."query[target_field]=value"
                        ],
                        "fields" => ["name", "code"]
                    ]
                ];
            }

            return response()->json($response,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($item_category_id){
            $item_category = ItemCategory::find($item_category_id);    
        }

        if(!$item_category_id && $query){
            $item_categories = ItemCategory::where($query)->get();
            
            // Check result is has many records
            if(count($item_categories) > 1){
                return response()->json([
                    'data' => $item_categories,
                    'message' => "Request has multiple record."
                ], Response::HTTP_CONFLICT);
            }

            $item_category = $item_categories->first();
        }

        if(!$item_category){
            $response = ["message" => "No record found."];

            if(self::$is_development){
                $response = [
                    'message' => "No record found.",
                    "metadata" => [
                        "methods" => "[GET, PUT, DELETE]",
                        "formats" => [
                            env("SERVER_DOMAIN")."/api/".self::$module."?id=1",
                            env("SERVER_DOMAIN")."/api/".self::$module."query[target_field]=value"
                        ],
                        "fields" => ["name", "code"]
                    ]
                ];
            }

            return response()->json($response, Response::HTTP_NOT_FOUND);
        }

        $item_category->update(['deleted_at' => now()]);

        $response = [];

        if(self::$is_development){
            $response = [
                "message" => "Successfully deleted record.",
                "metadata" => [
                    "content" => "Use status NO_CONTENT (204) for success handling request in production.",
                    "success_validation" => "condition(status >= 200 && status < 300)"
                ]
            ];
        }
        
        return response()->json( $response, Response::HTTP_NO_CONTENT);
    }
}
