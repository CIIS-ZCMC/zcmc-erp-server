<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpApplicationRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Models\AopApplication;
use App\Models\PpmpApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PpmpApplicationController extends Controller
{
    private $is_development;

    private $module = 'ppmp_applicationss';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    public function index(Request $request)
    {
        $year = $request->query('year', now()->year + 1);

        $ppmp_application = PpmpApplication::with([
            'ppmpItems' => function ($query) {
                $query->with([
                    'item',
                    'procurementMode',
                    'itemRequest',
                    'activities'
                ]);
            },
            'aopApplication' => function ($query) {
                $query->where('sector_id', 33);
            }
        ])->first();

        if (!$ppmp_application) {
            return response()->json([
                'data' => (object) [],
                'message' => 'No PPMP Application found.'
            ], Response::HTTP_OK);
        }

        $itemCount = $ppmp_application->ppmpItems->count();
        $activityCount = $ppmp_application->ppmpItems
            ->flatMap(fn($item): mixed => $item->activities)
            ->count();

        $totalQuantity = $ppmp_application->ppmpItems->sum('total_quantity');
        $totalBudget = $ppmp_application->ppmpItems->sum('estimated_budget');

        $data = [
            'ppmp_application' => $ppmp_application ?? null,
            'item_count' => $itemCount ?? null,
            'activity_count' => $activityCount ?? null,
            'total_quantity' => $totalQuantity ?? null,
            'total_budget' => $totalBudget ?? null,
            'year' => $ppmp_application->created_at->format('Y')
        ];

        return response()->json([
            'data' => $data,
            'message' => 'PPMP Application retrieved successfully.'
        ], Response::HTTP_OK);
    }

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

    public function show(PpmpApplication $ppmpApplication)
    {
        return response()->json(new PpmpApplicationResource($ppmpApplication), Response::HTTP_OK);
    }

    public function update(PpmpApplicationRequest $request, PpmpApplication $ppmpApplication)
    {
        $data = $request->all();
        $ppmpApplication->update(attributes: $data);

        return response()->json([
            'data' => new PpmpApplicationResource($data),
            'message' => 'PPMP Application updated successfully.',
        ], Response::HTTP_OK);
    }

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
