<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpApplicationRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Models\AopApplication;
use App\Models\PpmpApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

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
class PpmpApplicationController extends Controller
{
    private $is_development;

    private $module = 'ppmp_applicationss';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
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
    public function index(Request $request)
    {
        $ppmp_application = PpmpApplication::with([
            'ppmpItems' => function ($query) {
                $query->with([
                    'item',
                    'procurementMode',
                    'itemRequest',
                    'activities'
                ]);
            },
        ])->latest()->first();

        $itemCount = $ppmp_application->ppmpItems->count();
        $activityCount = $ppmp_application->ppmpItems
            ->flatMap(fn($item): mixed => $item->activities)
            ->count();

        $totalQuantity = $ppmp_application->ppmpItems->sum('total_quantity');
        $totalBudget = $ppmp_application->ppmpItems->sum('estimated_budget');

        if (!$ppmp_application) {
            return response()->json([
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'ppmp_application' => $ppmp_application,
            'item_count' => $itemCount,
            'activity_count' => $activityCount,
            'total_quantity' => $totalQuantity,
            'total_budget' => $totalBudget,
        ];

        return response()->json([
            'data' => $data,
            'message' => 'PPMP Application retrieved successfully.'
        ], Response::HTTP_OK);
    }

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
    public function store(PpmpApplicationRequest $request)
    {
        $budget_officer_id = User::budgetOfficer();

        if (!$budget_officer_id) {
            return response()->json([
                'message' => "Budget Officer not found.",
            ], Response::HTTP_NOT_FOUND);
        }

        foreach ($request->aop_application_id as $aop_id) {
            $aop = AopApplication::find($aop_id);

            if ($aop->isEmpty()) {
                return response()->json([
                    'message' => "AOP Application with ID {$aop_id} not found.",
                ], Response::HTTP_NOT_FOUND);
            }

            $data = new PpmpApplication();
            $data->aop_application_id = $aop->id;
            $data->user_id = $aop->user_id;
            $data->division_chief_id = $aop->division_chief_id;
            $data->budget_officer_id = $budget_officer_id->head_id;
            $data->ppmp_total = strip_tags($request['ppmp_total']);
            $data->remarks = strip_tags($request['remarks']);
            $data->save();
        }

        return response()->json([
            'data' => new PpmpApplicationResource($data),
            'message' => 'PPMP Application created successfully.',
        ], Response::HTTP_CREATED);

    }

    #[OA\Get(
        path: "/api/activity-comments/{id}",
        summary: "Show specific activity comment",
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
                response: Response::HTTP_OK,
                description: "Successful operation",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            )
        ]
    )]
    public function show(PpmpApplication $ppmpApplication)
    {
        return response()->json(new PpmpApplicationResource($ppmpApplication), Response::HTTP_OK);
    }

    #[OA\Put(
        path: "/api/activity-comments/{id}",
        summary: "Update an activity comment",
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
        requestBody: new OA\RequestBody(
            description: "Comment data",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: "Comment updated",
                content: new OA\JsonContent(ref: "#/components/schemas/ActivityComment")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: "Comment not found"
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: "Validation error"
            )
        ]
    )]
    public function update(PpmpApplicationRequest $request, PpmpApplication $ppmpApplication)
    {
        $data = $request->all();
        $ppmpApplication->update(attributes: $data);

        return response()->json([
            'data' => new PpmpApplicationResource($data),
            'message' => 'PPMP Application updated successfully.',
        ], Response::HTTP_OK);
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
    )]
    public function destroy(PpmpApplication $ppmpApplication)
    {
        // Check if the record exists
        if ($ppmpApplication->isEmpty()) {
            return response()->json([
                'message' => 'Record not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Perform the deletion
        $ppmpApplication->delete();

        return response()->json([
            'message' => 'PPMP Application deleted successfully.',
        ], Response::HTTP_OK);
    }
}
