<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Models\AopApplication;
use App\Models\FunctionObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AopApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.functionObjective.function',
                'applicationObjectives.functionObjective.objective',
                'applicationObjectives.othersObjective',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.responsiblePeople.user'
            ])
            ->get();
        return AopApplicationResource::collection($aopApplications);
    }

    /**
     * Store a newly created resource in storage.
     */
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


                $functionObjective = FunctionObjective::where('objective_id', $objectiveData['objective_id'])
                    ->where('function_id', $objectiveData['function'])
                    ->first();

                if (!$functionObjective) {

                    return response()->json(['error' => 'FunctionObjective not found'], 404);
                }


                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'function_objective_id' => $functionObjective->id,
                    'objective_code'       => $objectiveData['objective_code'],
                    'success_indicator_id'  => $objectiveData['success_indicator_id'],
                ]);


                if ($applicationObjective->functionObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->othersObjective()->create([
                        'description' => $objectiveData['others_objective'],
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
                        ' ' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);


                    $activity->resources()->createMany($activityData['resources']);


                    $activity->responsiblePeople()->createMany($activityData['responsible_people']);
                }
            }


            DB::commit();

            return response()->json(['message' => 'AOP Application created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AopApplication $aopApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AopApplication $aopApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AopApplication $aopApplication)
    {
        //
    }
}
