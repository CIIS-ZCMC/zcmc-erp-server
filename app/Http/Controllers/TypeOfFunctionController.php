<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\TypeOfFunctionRequest;
use App\Http\Resources\TypeOfFunctionResource;
use App\Http\Resources\TypeOfFunctionsWithObjectiveAndSuccessIndicatorsResource;
use App\Models\TypeOfFunction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeOfFunctionController extends Controller
{
    private $is_development;

    private $module = 'type-of-functions';
    
    private $methods = '[GET, POST, PUT, DELETE]';

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

    protected function updateBulk(Request $request): JsonResponse
    {
        $type_of_functions = $request->query('id') ?? null;
        
        if (count($type_of_functions) !== count($request->input('type_of_functions'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of type of functions provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->module, $this->is_development)
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
            return TypeOfFunctionResource::collection($updated_type_of_functions)
                ->additional([
                    "meta" => [
                        "methods" => $this->methods,
                        "issue" => $errors
                    ],
                    "message" => "Partial update completed with errors.",
                ])
                ->response()
                ->setStatusCode(Response::HTTP_MULTI_STATUS);
        }

        
        return TypeOfFunctionResource::collection($updated_type_of_functions)
            ->additional([
                "meta" => ["methods" => $this->methods],
                "message" => "Successfully updated ".count($updated_type_of_functions)." type of functions.",
            ])
            ->response();
    }

    protected function indexWithObjectivesAndSuccessIndicators(Request $request)
    {
        $type_of_functions = TypeOfFunction::all();

        return TypeOfFunctionsWithObjectiveAndSuccessIndicatorsResource::collection($type_of_functions)
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Success fetch type of functions."
            ])->response();
    }

    public function index(Request $request)
    {
        $with_sub_data = $request->query('with_sub_data');


        if($with_sub_data){
            return $this->indexWithObjectivesAndSuccessIndicators($request);
        }

        $type_of_functions = TypeOfFunction::all();

        return TypeOfFunctionResource::collection($type_of_functions)
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Success fetch type of functions."
            ])->response();
    }

    public function store(TypeOfFunctionRequest $request)
    {
        // Bulk insert
        if($request->type_of_functions !== null && count($request->type_of_functions) > 0){
            $type_of_functions_result = [];

            foreach($request->type_of_functions as $function){
                $type = strip_tags($function['type']);
                $new_type_of_function = TypeOfFunction::create(['type' => $type]);
                $type_of_functions_result[] = $new_type_of_function;
            }

            return TypeOfFunctionResource::collection($type_of_functions_result)
                ->additional([
                    "meta" => [
                        "methods" => $this->methods
                    ],
                    "message" => "Successfully register types."
                ])
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        }   

        $type = strip_tags($request->type);

        $new_type_of_function = TypeOfFunction::create(['type' => $type]);

        return (new TypeOfFunctionResource($new_type_of_function))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Success register type of function."
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request):Response    
    {
        $type_of_functions = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$type_of_functions) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->module, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $type_of_functions = is_array($type_of_functions) ? $type_of_functions : [$type_of_functions];
        
        // For bulk update - validate items array matches IDs count
        if ($request->has('type_of_functions')) {
            return $this->updateBulk($request);
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
        
        $type = strip_tags($request->type);
        $type_of_function->update(['type' => $type]);

        return (new TypeOfFunctionResource($type_of_function))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Success update type of function."
            ])
            ->response();
    }

    public function trash(Request $request)
    {
        $search = $request->get("search");

        if($search){
            return TypeOfFunctionResource::collection(TypeOfFunction::onlyTrashed()->where('type', "like", "%".$search."%")->get())
                ->additional([
                    "meta" => [
                        "methods" => $this->methods
                    ],
                    "message" => "Successfully restore data."
                ]);
        }

        return TypeOfFunctionResource::collection(TypeOfFunction::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully restore data."
            ]);
    }

    public function restore($id, Request $request): TypeOfFunctionResource
    {
        TypeOfFunction::withTrashed()->where("id", $id)->restore();

        return (new TypeOfFunctionResource(TypeOfFunction::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully restore type of function data."
            ]);
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
                    "metadata" => MetadataComposerHelper::compose('delete', $this->module, $this->is_development),
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
            $deleted_count = TypeOfFunction::whereIn('id', $valid_ids)->delete();
    
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

        $type_of_function->delete();

        return response()->json([
            "message" => "Successfully deleted function type.",
            "deleted_id" => $type_of_function->id,
            "function_name" => $type_of_function->name
        ], Response::HTTP_OK);
    }
}
