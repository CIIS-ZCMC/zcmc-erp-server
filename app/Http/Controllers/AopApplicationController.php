<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\ShowAopApplicationResource;
use App\Http\Resources\AopRequestResource;
use App\Models\AopApplication;
use App\Models\FunctionObjective;
use App\Models\SuccessIndicator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AopApplicationController extends Controller
{

    public function index()
    {
        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.objective',
                'applicationObjectives.othersObjective',
                'applicationObjectives.successIndicator',
                'applicationObjectives.otherSuccessIndicator',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.responsiblePeople.user',
            ])
            ->get();

        return AopApplicationResource::collection($aopApplications);
    }


    public function store(AopApplicationRequest $request)
    {

        $validatedData = $request->validated();


        DB::beginTransaction();

        try {
            // Create AOP Application
            $aopApplication = AopApplication::create([
                // 'user_id' => $validatedData['user_id'],
                // 'division_chief_id' => $validatedData['division_chief_id'],
                // 'mcc_chief_id' => $validatedData['mcc_chief_id'],
                // 'planning_officer_id' => $validatedData['planning_officer_id'],
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission'],
                'status' => $validatedData['status'],
                'has_discussed' => $validatedData['has_discussed'],
                'remarks' => $validatedData['remarks'] ?? null,
            ]);


            foreach ($validatedData['application_objectives'] as $objectiveData) {


                // $functionObjective = FunctionObjective::where('objective_id', $objectiveData['objective_id'])
                //     ->where('function_id', $objectiveData['function'])
                //     ->first();

                // if (!$functionObjective) {

                //     return response()->json(['error' => 'FunctionObjective not found'], 404);
                // }


                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'objective_id'         => $objectiveData['objective_id'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                ]);


                if ($applicationObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->othersObjective()->create([
                        'description' => $objectiveData['others_objective'],
                    ]);
                }

                $successIndicator = SuccessIndicator::find($objectiveData['success_indicator_id']);

                if (
                    $successIndicator &&
                    $successIndicator->description === 'Others' &&
                    isset($objectiveData['other_success_indicator'])
                ) {
                    $applicationObjective->otherSuccessIndicator()->create([
                        'description' => $objectiveData['other_success_indicator'],
                    ]);
                }

                foreach ($objectiveData['activities'] as $activityData) {
                    $activity = $applicationObjective->activities()->create([
                        'activity_uuid' => $activityData['activity_uuid'],
                        'activity_code' => $activityData['activity_code'],
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);


                    $activity->resources()->createMany($activityData['resources']);


                    $activity->responsiblePeople()->createMany($activityData['responsible_person']);
                }
            }


            DB::commit();

            return response()->json(['message' => 'AOP Application created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAopApplicationSummary($aopApplicationId)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople',
        ])->findOrFail($aopApplicationId);

        $totalObjectives = $aopApplication->applicationObjectives->count();

        $totalActivities = $aopApplication->applicationObjectives->flatMap(function ($objective) {
            return $objective->activities;
        })->count();

        $totalResources = $aopApplication->applicationObjectives->flatMap(function ($objective) {
            return $objective->activities->flatMap->resources;
        })->count();

        $totalResponsiblePeople = $aopApplication->applicationObjectives->flatMap(function ($objective) {
            return $objective->activities->flatMap->responsiblePeople;
        })->count();

        return response()->json([
            'total_objectives'         => $totalObjectives,
            'total_activities'         => $totalActivities,
            'total_resources'          => $totalResources,
            'total_responsible_people' => $totalResponsiblePeople,
        ]);
    }


    public function show(AopApplication $aopApplication)
    {
        $aopApplication->load([
            'user',
            'divisionChief',
            'mccChief',
            'planningOfficer',
            'applicationObjectives',
            'applicationObjectives.functionObjective',
            'applicationObjectives.activities'
        ]);

        return new ShowAopApplicationResource($aopApplication);
    }
}
