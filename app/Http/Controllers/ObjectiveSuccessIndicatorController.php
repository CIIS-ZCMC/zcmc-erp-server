<?php

namespace App\Http\Controllers;

use App\Helpers\MetadataComposerHelper;
use App\Helpers\PaginationHelper;
use App\Http\Requests\GetObjectiveSuccessIndicatorRequest;
use App\Http\Requests\GetWithPaginatedSearchModeRequest;
use App\Http\Resources\ObjectiveSuccessIndicatorResource;
use App\Models\Objective;
use App\Models\ObjectiveSuccessIndicator;
use App\Models\OtherObjective;
use App\Models\SuccessIndicator;
use App\Models\Target;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Ramsey\Uuid\Type\Integer;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use App\Models\OtherSuccessIndicator;

#[OA\Schema(
    schema: "ActivityComment",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "activity_id", type: "integer"),
        new OA\Property(property: "user_id", type: "integer", nullable: true),
        new OA\Property(property: "content", type: "string"),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time"
        ),
        new OA\Property(
            property: "updated_at",
            type: "string",
            format: "date-time"
        )
    ]
)]
class ObjectiveSuccessIndicatorController extends Controller
{
    private $is_development;

    private $module = 'objective-success-indicators';

    private $methods = '[GET, POST, PUT, DELETE]';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanObjectiveSuccessIndicatorData(array $data): array
    {
        $cleanData = [];

        if (isset($data['objective_id'])) {
            $cleanData['objective_id'] = strip_tags($data['objective_id']);
        }

        if (isset($data['success_indicator_id'])) {
            $cleanData['success_indicator_id'] = strip_tags($data['success_indicator_id']);
        }

        return $cleanData;
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $start = microtime(true);

        $validated = $request->validate([
            'search' => 'required|string|min:2|max:100',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1|max:100'
        ]);

        $searchTerm = '%' . trim($validated['search']) . '%';
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

    protected function singleRecord($objective_success_indicator_id, $start): JsonResponse
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

    #[OA\Get(
        path: "/api/activity-comments",
        summary: "List all activity comments",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Items per page",
                required: false,
                schema: new OA\Schema(type: "integer", default: 15)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/ActivityComment")
                )
            )
        ]
    )]
    public function index(GetWithPaginatedSearchModeRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $start = microtime(true);
        $objective_success_indicator_id = $request->query('id');
        $search = $request->search;
        $mode = $request->mode;

        if ($objective_success_indicator_id) {
            return $this->singleRecord($objective_success_indicator_id, $start);
        }

        if ($mode && $mode === 'selection') {
            return $this->all();
        }

        if ($search) {
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
        if (!$this->doesObjectiveExist($objective_id)) {
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
        if (!$this->doesSuccessIndicatorExist($success_indicator_id)) {
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

    #[OA\Post(
        path: "/api/activity-comments",
        summary: "Create a new activity comment",
        tags: ["Activity Comments"],
        requestBody: new OA\RequestBody(
            description: "Comment data",
            required: true,
            content: new OA\JsonContent(
                required: ["activity_id", "content"],
                properties: [
                    new OA\Property(property: "activity_id", type: "integer"),
                    new OA\Property(property: "content", type: "string"),
                    new OA\Property(property: "user_id", type: "integer", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: "Comment created",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
    public function store(Request $request): Response
    {
        $objective_id = $request->objective_id;
        $success_indicator_id = $request->success_indicator_id;
        $objective = $request->objective;
        $success_indicator = $request->success_indicator;
        $new_objective_success_indicator = null;

        // Both Primary key is given
        if ($objective_id && $success_indicator_id) {

            if (!($this->doesObjectiveExist($objective_id) || $this->doesSuccessIndicatorExist($success_indicator_id))) {
                return response()->json(['message' => "Data of objective/success indicator doesn't exist."], Response::HTTP_NOT_FOUND);
            }

            $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                'objective_id' => $objective_id,
                'success_indicator_id' => $success_indicator_id
            ]);
        }

        if ($objective_id && $success_indicator) {

            $new_objective_success_indicator = $this->registerOSIWithoutExistingSuccessIndicator($objective_id, $success_indicator);
        }

        if ($success_indicator_id && $objective) {

            $new_objective_success_indicator = $this->registerOSIWithoutExistingSuccessIndicator($objective_id, $success_indicator);
        }

        if ($success_indicator && $objective) {
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

    #[OA\Delete(
        path: "/api/activity-comments/{id}",
        summary: "Delete an activity comment",
        tags: ["Activity Comments"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Comment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: "Comment deleted"
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            )
        ]
    )]    public function destroy(Request $request, ObjectiveSuccessIndicator $objectiveSuccessIndicator)
    {
        $objectiveSuccessIndicator->update(['deleted_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    #[OA\Put(
        path: "/api/objective-success-indicators/{id}",
        summary: "Update an objective success indicator",
        tags: ["Objective Success Indicators"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Objective Success Indicator ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Objective Success Indicator data",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "objective_id", type: "integer"),
                    new OA\Property(property: "success_indicator_id", type: "integer"),
                    new OA\Property(property: "objective", type: "object"),
                    new OA\Property(property: "success_indicator", type: "object")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Objective Success Indicator updated",
                content: new OA\JsonContent(ref: "#/components/schemas/ObjectiveSuccessIndicator")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Objective Success Indicator not found"
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
    public function update(Request $request, ObjectiveSuccessIndicator $objectiveSuccessIndicator): JsonResponse
    {
        $start = microtime(true);
        $objective_id = $request->objective_id;
        $success_indicator_id = $request->success_indicator_id;
        $objective = $request->objective;
        $success_indicator = $request->success_indicator;
        $updated = false;

        // Update with both primary keys
        if ($objective_id && $success_indicator_id) {
            if (!($this->doesObjectiveExist($objective_id) && $this->doesSuccessIndicatorExist($success_indicator_id))) {
                return response()->json(['message' => "Data of objective/success indicator doesn't exist."], Response::HTTP_NOT_FOUND);
            }

            $objectiveSuccessIndicator->update([
                'objective_id' => $objective_id,
                'success_indicator_id' => $success_indicator_id
            ]);
            $updated = true;
        }

        // Update with objective_id and new success indicator
        if ($objective_id && $success_indicator && !$updated) {
            if (!$this->doesObjectiveExist($objective_id)) {
                return response()->json(['message' => "Data of objective doesn't exist."], Response::HTTP_NOT_FOUND);
            }

            $new_success_indicator = SuccessIndicator::create($success_indicator);
            $objectiveSuccessIndicator->update([
                'objective_id' => $objective_id,
                'success_indicator_id' => $new_success_indicator->id
            ]);
            $updated = true;
        }

        // Update with success_indicator_id and new objective
        if ($success_indicator_id && $objective && !$updated) {
            if (!$this->doesSuccessIndicatorExist($success_indicator_id)) {
                return response()->json(['message' => "Data of success indicator doesn't exist."], Response::HTTP_NOT_FOUND);
            }

            $new_objective = Objective::create($objective);
            $objectiveSuccessIndicator->update([
                'objective_id' => $new_objective->id,
                'success_indicator_id' => $success_indicator_id
            ]);
            $updated = true;
        }

        // Update with new objective and new success indicator
        if ($objective && $success_indicator && !$updated) {
            $new_objective = Objective::create($objective);
            $new_success_indicator = SuccessIndicator::create($success_indicator);

            $objectiveSuccessIndicator->update([
                'objective_id' => $new_objective->id,
                'success_indicator_id' => $new_success_indicator->id
            ]);
            $updated = true;
        }

        if (!$updated) {
            return response()->json(['message' => "No valid update data provided."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Refresh the model to get updated relationships
        $objectiveSuccessIndicator->refresh();

        return (new ObjectiveSuccessIndicatorResource($objectiveSuccessIndicator))
            ->additional([
                "meta" => [
                    "methods" => $this->methods,
                    'time_ms' => round((microtime(true) - $start) * 1000)
                ],
                "message" => "Successfully updated record."
            ])->response();
    }

    /**
     * Update an objective and success indicator for the approver module
     * 
     * This method handles updating an existing objective success indicator with either
     * existing objective/success indicator IDs or creating new "other" entries when provided.
     * It handles transaction management to ensure data integrity.
     * 
     * @param Request $request The HTTP request containing validation data
     * @param int $id The ID of the objective success indicator to update
     * @return JsonResponse Returns the updated resource or error response
     */
    public function updateForApproverModule(Request $request, $id)
    {
        $data = $request->validate([
            'application_objective_id' => 'required_without_all:other_objective,other_success_indicator|integer',
            'objective_id' => 'nullable|integer',
            'success_indicator_id' => 'nullable|integer',
            'other_objective' => 'nullable|string',
            'other_success_indicator' => 'nullable|string',
        ]);

        $start = microtime(true);
        $objectiveSuccessIndicator = ObjectiveSuccessIndicator::find($id);

        if (!$objectiveSuccessIndicator) {
            return response()->json(['message' => 'Objective Success Indicator not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the passed ids exist when provided
        if (isset($data['objective_id'])) {
            $isObjectiveIdExist = Objective::where('id', $data['objective_id'])->exists();
            if (!$isObjectiveIdExist) {
                return response()->json(['message' => 'Objective not found'], Response::HTTP_NOT_FOUND);
            }
        }

        if (isset($data['success_indicator_id'])) {
            $isSuccessIndicatorIdExist = SuccessIndicator::where('id', $data['success_indicator_id'])->exists();
            if (!$isSuccessIndicatorIdExist) {
                return response()->json(['message' => 'Success Indicator not found'], Response::HTTP_NOT_FOUND);
            }
        }

        DB::beginTransaction();
        try {

            // Only create other objective if it's provided
            $otherObjective = null;
            if (!empty($data['other_objective'])) {
                $otherObjective = OtherObjective::create([
                    'application_objective_id' => $data['application_objective_id'],
                    'description' => $data['other_objective']
                ]);

                if (!$otherObjective) {
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to create other objective'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            // Only create other success indicator if it's provided
            $otherSuccessIndicator = null;
            if (!empty($data['other_success_indicator'])) {
                $otherSuccessIndicator = OtherSuccessIndicator::create([
                    'application_objective_id' => $data['application_objective_id'],
                    'description' => $data['other_success_indicator']
                ]);

                if (!$otherSuccessIndicator) {
                    DB::rollBack();
                    return response()->json(['message' => 'Failed to create other success indicator'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            
            $objectiveSuccessIndicator->update([
                'objective_id' => $data['objective_id'] ?? $objectiveSuccessIndicator->objective_id,
                'success_indicator_id' => $data['success_indicator_id'] ?? $objectiveSuccessIndicator->success_indicator_id
            ]);

            DB::commit();

            return (new ObjectiveSuccessIndicatorResource($objectiveSuccessIndicator))
                ->additional([
                    "meta" => [
                        "methods" => $this->methods,
                        'time_ms' => round((microtime(true) - $start) * 1000)
                    ],
                    "message" => "Successfully updated record."
                ])->response();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update objective and success indicator',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
