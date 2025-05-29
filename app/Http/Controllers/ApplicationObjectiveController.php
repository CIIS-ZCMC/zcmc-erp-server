<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Activity;
use App\Models\ApplicationObjective;
use App\Http\Resources\ManageAopRequestResource;
use App\Http\Resources\ShowObjectiveResource;
use App\Models\SuccessIndicator;
use Illuminate\Support\Facades\DB;
use App\Models\OtherObjective;
use App\Models\OtherSuccessIndicator;
use App\Models\AopApplication;

class ApplicationObjectiveController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(ApplicationObjective $applicationObjective)
    {
        //
    }

    public function update(Request $request, ApplicationObjective $applicationObjective)
    {
        //
    }

    public function destroy(ApplicationObjective $applicationObjective)
    {
        //
    }

    /**
     * Show a specific AOP request with objectives and activities
     *
     * Retrieves application objectives with their related activities, comments,
     * parent objective, and success indicators for a given AOP application ID.
     *
     * @param int $id The AOP application ID
     * @return JsonResponse Collection of application objectives with related data
     */
    public function manageAopRequest(int $id): JsonResponse
    {
        $applicationObjectives = ApplicationObjective::with([
            'activities',
            'activities.comments',
            'objective',
            'otherObjective',
            'objective.typeOfFunction', // Added typeOfFunction relationship
            'successIndicator',
            'otherSuccessIndicator',
        ])
            ->where('aop_application_id', $id)
            ->whereNull('deleted_at')
            ->get();

        if (!$applicationObjectives) {
            return response()->json([
                'message' => 'AOP request not found',
            ], Response::HTTP_NOT_FOUND);
        }


        return response()->json([
            'message' => 'AOP request retrieved successfully',
            'data' => [
                'application' => AopApplication::where('id', $id)->first(),
                'objectives' => ManageAopRequestResource::collection($applicationObjectives)
            ],
        ], Response::HTTP_OK);
    }


    public function showObjectiveActivity(int $id): JsonResponse
    {
        $activity = Activity::with([
            'comments',
            'target',
            'resources',
            'responsiblePeople',
        ])
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$activity) {
            return response()->json([
                'message' => 'Objective details not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Activity details retrieved successfully',
            'data' => new ShowObjectiveResource($activity),
        ], Response::HTTP_OK);
    }

    public function editObjectiveAndSuccessIndicator(Request $request)
    {
        try {

            $request->validate([
                'aop_application_id' => 'required|exists:aop_applications,id',
                'objective_description' => 'required|string',
                'success_indicator_description' => 'required|string',
            ]);


            // Begin transaction to ensure all operations succeed or fail together
            DB::beginTransaction();

            try {
                // Find the application objective using AopApplication model
                $aopApplication = AopApplication::findOrFail($request->aop_application_id);

                // Get the application objective related to this aop application with specific objective and success indicator IDs
                $applicationObjective = ApplicationObjective::with(['otherObjective', 'otherSuccessIndicator'])
                    ->where('aop_application_id', $aopApplication->id)
                    ->where('objective_id', 23)
                    ->where('success_indicator_id', 36)
                    ->first();

                if (!$applicationObjective) {
                    return response()->json([
                        'message' => 'Application objective not found',
                    ], Response::HTTP_NOT_FOUND);
                }

                // Get or create otherObjective
                $otherObjective = $applicationObjective->otherObjective;

                if (!$otherObjective) {
                    $otherObjective = new OtherObjective([
                        'application_objective_id' => $applicationObjective->id,
                    ]);
                }
                $otherObjective->description = $request->objective_description;
                $otherObjective->save();

                // Get or create otherSuccessIndicator
                $otherSuccessIndicator = $applicationObjective->otherSuccessIndicator;
                if (!$otherSuccessIndicator) {
                    $otherSuccessIndicator = new OtherSuccessIndicator([
                        'application_objective_id' => $applicationObjective->id,
                    ]);
                }
                $otherSuccessIndicator->description = $request->success_indicator_description;
                $otherSuccessIndicator->save();

                // Reload the application objective with its relationships to return
                $applicationObjective->load(['otherObjective', 'otherSuccessIndicator', 'objective', 'successIndicator']);

                // Commit the transaction if all operations succeed
                DB::commit();

                return response()->json([
                    'message' => 'Objective and success indicator updated successfully',
                    'data' => new ManageAopRequestResource($applicationObjective),
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                // Rollback transaction if any operation fails
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Record not found',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the objective and success indicator',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
