<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GetObjectiveSuccessIndicatorRequest;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Resources\ObjectiveSuccessIndicatorResource;
use App\Models\Objective;
use App\Models\ObjectiveSuccessIndicator;
use App\Models\SuccessIndicator;
use App\Models\Target;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Ramsey\Uuid\Type\Integer;
use Symfony\Component\HttpFoundation\Response;

class ObjectiveSuccessIndicatorController extends Controller
{
    private $is_development;

    private $module = 'objective-success-indicators';

    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    public function search(Request $request):AnonymousResourceCollection
    {
        $start = microtime(true);
        
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);
        
        $searchTerm = '%'.trim($validated['search']).'%';
        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $results = ObjectiveSuccessIndicator::whereHas('objective', fn($q) => $q->where('code', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%"))
            ->orWhereHas('successIndicator', fn($q) => $q->where('code', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%"))
            ->with(['objective', 'successIndicator'])
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ObjectiveSuccessIndicatorResource::collection($results)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'search' => [
                        'term' => $validated['search'],
                        'time_ms' => round((microtime(true) - $start) * 1000), // in milliseconds
                    ],
                    'pagination' => [
                        'total' => $results->total(),
                        'per_page' => $results->perPage(),
                        'current_page' => $results->currentPage(),
                        'last_page' => $results->lastPage(),
                    ]
                ],
                'message' => 'Search completed successfully'
            ]);
    }

    public function all()
    {
        $start = microtime(true);

        $objective_success_indicator = ObjectiveSuccessIndicator::with(['objective', 'successIndicator'])->get();

        return ObjectiveSuccessIndicatorResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }

    public function pagination(Request $request)
    {
        $start = microtime(true);
        
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $perPage = $validated['per_page'] ?? 15;
        $page = $validated['page'] ?? 1;

        $objective_success_indicator = ObjectiveSuccessIndicator::with(['objective', 'successIndicator'])
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return ObjectiveSuccessIndicatorResource::collection($objective_success_indicator)
            ->additional([
                'meta' => [
                    'methods' => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000),
                    'pagination' => [
                        'total' => $objective_success_indicator->total(),
                        'per_page' => $objective_success_indicator->perPage(),
                        'current_page' => $objective_success_indicator->currentPage(),
                        'last_page' => $objective_success_indicator->lastPage(),
                    ]
                ],
                'message' => 'Successfully retrieve all records.'
            ]);
    }  
    
    protected function singleRecord($objective_success_indicator_id, $start):JsonResponse
    {
        $objectiveSuccessIndicator = ObjectiveSuccessIndicator::find($objective_success_indicator_id);
            
        if (!$objectiveSuccessIndicator) {
            return response()->json(["message" => "Item unit not found."], Response::HTTP_NOT_FOUND);
        }
    
        return (new ObjectiveSuccessIndicatorResource($objectiveSuccessIndicator))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully retrieved record."
            ])->response();
    }

    public function index(GetWithPaginatedSearchModeRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $start = microtime(true);
        $objective_success_indicator_id = $request->query('id');
        $search = $request->search;
        $mode = $request->mode;

        if($objective_success_indicator_id){
            return $this->singleRecord($objective_success_indicator_id, $start);
        }

        if($mode && $mode === 'selection'){
            return $this->all();
        }
        
        if($search){
            return $this->search($request);
        }

        return $this->pagination($request);
    }

    protected function doesObjectiveExist(Integer $objective_id): bool
    {
        return Objective::where('id', $objective_id)->exists();
    }

    protected function doesSuccessIndicatorExist(Integer $success_indicator_id): bool
    {
        return SuccessIndicator::where('id', $success_indicator_id)->exists();
    }

    protected function registerOSIWithoutExistingSuccessIndicator(Integer $objective_id, $success_indicator): ObjectiveSuccessIndicator
    {
        if(!$this->doesObjectiveExist($objective_id)){
            return response()->json(['message' => "Data of objective doesn't exist."], Response::HTTP_NOT_FOUND);
        }

        $new_success_indicator = SuccessIndicator::create($success_indicator);

        return ObjectiveSuccessIndicator::create([
            'objective_id' => $objective_id,
            'success_indicator_id' => $new_success_indicator->id
        ]);
    }

    protected function registerOSIWithoutExistingObjective(Integer $success_indicator_id, $objective): ObjectiveSuccessIndicator
    {
        if(!$this->doesSuccessIndicatorExist($success_indicator_id)){
            return response()->json(['message' => "Data of success indicator doesn't exist."], Response::HTTP_NOT_FOUND);
        }

        $new_objective = Objective::create($objective);

        return ObjectiveSuccessIndicator::create([
            'objective_id' => $new_objective->id,
            'success_indicator_id' => $success_indicator_id
        ]);
    }

    /**
     * Registration of Objective Success Indicator (OSI)
     * 
     * 1. [Objective] and [SuccessIndicator] can Exist so registration can be base on objective_id and success_indicator_id. (COMPLETE)
     * 2. [Objective] exist [SuccessIndicator] not, registration will be base on objective_id and success indicator data.
     * 3. [SuccessIndicator] exist [Objective] not, registration will be base on success_indicator_id and objective data.
     * 4. [SuccessIndicator] and Objective may not exist, registration will base on success indicator dat and objective data
     * 
     * To deliver a flexible single registration of data.
     * Body Structure: 
     * {
     *  objective_id: primary_key // optional
     *  success_indicator: primary_key // optional
     *  objective: {
     *      code: code_value // optional
     *      description: description_value // required
     *  },
     *  success_indicator: {
     *      code: code_value // optional
     *      description: description_value // required
     *  }
     * }
     */

    public function store(Request $request):Response
    {
        $objective_id = $request->objective_id;
        $success_indicator_id = $request->success_indicator_id;
        $objective = $request->objective;
        $success_indicator = $request->success_indicator;
        $new_objective_success_indicator = null;

        // Both Primary key is given
        if($objective_id && $success_indicator_id){

            if(!($this->doesObjectiveExist($objective_id) || $this->doesSuccessIndicatorExist($success_indicator_id))){
                return response()->json(['message' => "Data of objective/success indicator doesn't exist."], Response::HTTP_NOT_FOUND);
            }
            
            $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                'objective_id' => $objective_id,
                'success_indicator_id' => $success_indicator_id
            ]);
        }

        if($objective_id && $success_indicator){

            $new_objective_success_indicator = $this->registerOSIWithoutExistingSuccessIndicator($objective_id, $success_indicator);
        }

        if($success_indicator_id && $objective){

            $new_objective_success_indicator = $this->registerOSIWithoutExistingSuccessIndicator($objective_id, $success_indicator);
        }

        if($success_indicator && $objective){
            $new_objective = Objective::create($objective);
            $new_success_indicator = SuccessIndicator::create($success_indicator);

            $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                'objective_id' => $new_objective->id,
                'success_indicator_id' => $new_success_indicator->id
            ]);
        }
        
        return response()->json([
            "data" => new ObjectiveSuccessIndicatorResource($new_objective_success_indicator),
            "metadata" => [
                "methods" => ["GET, POST, DELETE"]
            ]
        ], Response::HTTP_CREATED);
    }

    public function destroy(Request $request, ObjectiveSuccessIndicator $objectiveSuccessIndicator)
    {
        $objectiveSuccessIndicator->update(['deleted_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
