<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Http\Requests\PurchaseTypeRequest;
use App\Http\Resources\PurchaseTypeDuplicateResource;
use App\Http\Resources\PurchaseTypeResource;
use App\Models\PurchaseType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PurchaseTypeController extends Controller
{
    private $is_development;

    private $module = 'purchase-types';

    private $methods = '[GET, POST, PUT, DELETE]';

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

    protected function storeBulk(Request $request): JsonResponse
    {
        $existing_purchase_types = [];
        $existing_items = PurchaseType::whereIn('code', collect($request->purchase_types)->pluck('code'))
            ->get(['code'])->toArray();

        // Convert existing items into a searchable format
        $existing_codes = array_column($existing_items, 'code');

        if(!empty($existing_items)){
            $existing_purchase_types = PurchaseTypeDuplicateResource::collection(PurchaseType::whereIn("code", $existing_codes)->get());
        }

        foreach ($request->purchase_types as $purchase_type) {
            if ( !in_array($purchase_type['code'], $existing_codes)) {
                $cleanData[] = [
                    "code" => strip_tags($purchase_type['code']),
                    "description" => isset($purchase_type['description']) ? strip_tags($purchase_type['description']) : null,
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

        return PurchaseTypeResource::collection($latest_purchase_types)
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    "duplicate_items" => $existing_purchase_types
                ],
                "message" => "Successfully created purchase types."
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    protected function updateBulk(Request $request): AnonymousResourceCollection|JsonResponse
    {   
        $purchase_types = $request->query('id') ?? null;

        if (count($purchase_types) !== count($request->input('purchase_types'))) {
            return response()->json([
                "message" => "Number of IDs does not match number of purchase_types provided.",
                "meta" => MetadataComposerHelper::compose('put', $this->module, $this->is_development)
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
            return PurchaseTypeResource::collection($updated_purchase_types)
                ->additional([
                    "meta" => [
                        "methods" => $this->methods,
                        "issue" => $errors
                    ],
                    "message" => "Partial update completed with errors."
                ]);
        }

        return PurchaseTypeResource::collection($updated_purchase_types)
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Partial update completed with errors."
            ]);
    }

    public function index(Request $request)
    {
        return PurchaseTypeResource::collection(PurchaseType::all())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieve purchase type list."
            ]);
    }

    public function store(PurchaseTypeRequest $request): JsonResponse
    {
        // Bulk Insert
        if ($request->purchase_types !== null || $request->purchase_types > 1) {
            return $this->storeBulk($request);
        }

        $cleanData = [
            "code" => strip_tags($request->input('code')),
            "description" => strip_tags($request->input('description')),
        ];
        
        $new_item = PurchaseType::create($cleanData);

        return (new PurchaseTypeResource($new_item))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully created purchase type."
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request): AnonymousResourceCollection|JsonResponse|PurchaseTypeResource    
    {
        $purchase_types = $request->query('id') ?? null;
        
        // Validate request has IDs
        if (!$purchase_types) {
            $response = ["message" => "ID parameter is required."];
            
            if ($this->is_development) {
                $response['meta'] = MetadataComposerHelper::compose('put', $this->module, $this->is_development);
            }
            
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Convert single ID to array for consistent processing
        $purchase_types = is_array($purchase_types) ? $purchase_types : [$purchase_types];
        
        // For bulk update - validate purchase_types array matches IDs count
        if ($request->has('purchase_types')) {
            return $this->updateBulk($request);
        }
        
        // Single item update
        if (count($purchase_types) > 1) {
            return response()->json([
                "message" => "Multiple IDs provided but no purchase type array for bulk update.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $purchase_type = PurchaseType::find($purchase_types[0]);
        
        if (!$purchase_type) {
            return response()->json([
                "message" => "PurchaseType not found."
            ], Response::HTTP_NOT_FOUND);
        }
        
        $cleanData = $this->cleanPurchaseTypeData($request->all());
        $purchase_type->update($cleanData);

        return (new PurchaseTypeResource($purchase_type))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Purchase Type updated successfully."
            ]);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = PurchaseType::onlyTrashed();

        if ($search) {
            $query->where('description', 'like', '%' . $search . '%');
        }
        
        return PurchaseTypeResource::collection(PurchaseType::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        PurchaseType::withTrashed()->where('id', $id)->restore();

        return (new PurchaseTypeResource(PurchaseType::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
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
                    "meta" => MetadataComposerHelper::compose('delete', $this->module, $this->is_development),
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
            $deleted_count = PurchaseType::whereIn('id', $found_ids)->delete();

            return response()->json([
                "message" => "Successfully deleted {$deleted_count} purchase type(s)."
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
        
        $purchase_type->delete();

        return response()->json([
            "message" => "Successfully deleted purchase type."
        ], Response::HTTP_OK);
    }
}
