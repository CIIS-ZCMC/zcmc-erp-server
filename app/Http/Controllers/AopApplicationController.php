<?php

namespace App\Http\Controllers;

use App\Http\Requests\AopApplicationRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\ShowAopApplicationResource;
use App\Http\Resources\ManageAopRequestResource;
use App\Http\Resources\AopRequestResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Models\AopApplication;
use App\Models\FunctionObjective;
use App\Models\PpmpItem;
use App\Models\PurchaseType;
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

        return $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.objective',
                'applicationObjectives.otherObjective',
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

                        'activity_code' => $activityData['activity_code'],
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);


                    $activity->resources()->createMany($activityData['resources']);


                    $activity->responsiblePeople()->createMany($activityData['responsible_people']);
                }
            }

            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');

            $ppmpTotal = 0;

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            $ppmpApplication = $aopApplication->ppmpApplication()->create([
                'user_id' => 1,
                'division_chief_id' => 1,
                'budget_officer_id' => 1,
                'ppmp_application_uuid' => Str::uuid(),
                'ppmp_total' => $ppmpTotal,
                'status' => 'pending',
                'remarks' => $request->remarks ?? null,
            ]);


            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $estimatedBudget = $resource->item->estimated_budget;
                            $totalAmount = $resource->quantity * $estimatedBudget;

                            PpmpItem::create([
                                'ppmp_application_id' => $ppmpApplication->id,
                                'item_id'             => $resource->item_id,
                                'total_quantity'      => $resource->quantity,
                                'estimated_budget'    => $estimatedBudget,
                                'total_amount'        => $totalAmount,
                                'remarks'             => null,
                            ]);
                        }
                    }
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
            'applicationObjectives.activities.comments', // Include comments
        ])->findOrFail($aopApplicationId);

        $totalObjectives = $aopApplication->applicationObjectives->count();

        $totalActivities = $aopApplication->applicationObjectives->flatMap(function ($objective) {
            return $objective->activities;
        });

        $totalResources = $totalActivities->flatMap->resources->count();
        $totalResponsiblePeople = $totalActivities->flatMap->responsiblePeople->count();
        $totalComments = $totalActivities->flatMap->comments->count();

        return response()->json([
            'total_objectives'         => $totalObjectives,
            'total_activities'         => $totalActivities->count(),
            'total_resources'          => $totalResources,
            'total_responsible_people' => $totalResponsiblePeople,
            'total_comments'           => $totalComments,
        ]);
    }


    public function update(AopApplicationRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $aopApplication = AopApplication::with([
                'applicationObjectives.activities.resources.item',
                'ppmpApplication',
            ])->findOrFail($id);


            $aopApplication->update($request->only([
                'mission',
                'status',
            ]));

            //Delete old nested relationships
            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    $activity->target()->delete();
                    $activity->resources()->delete();
                    $activity->responsiblePeople()->delete();
                }
                $objective->activities()->delete();
                $objective->otherObjective()->delete();
                $objective->otherSuccessIndicator()->delete();
            }
            $aopApplication->applicationObjectives()->delete();

            //Recreate all nested objectives and relations
            foreach ($request->application_objectives as $objectiveData) {
                $applicationObjective = $aopApplication->applicationObjectives()->create([
                    'objective_id' => $objectiveData['objective_id'],
                    'success_indicator_id' => $objectiveData['success_indicator_id'],
                    'objective_code' => $objectiveData['objective_code'] ?? null,
                ]);

                if (($applicationObjective->objective->description ?? null) === 'Others' && isset($objectiveData['others_objective'])) {
                    $applicationObjective->othersObjective()->create([
                        'description' => $objectiveData['others_objective'],
                    ]);
                }

                if (($applicationObjective->successIndicator->description ?? null) === 'Others' && isset($objectiveData['other_success_indicator'])) {
                    $applicationObjective->otherSuccessIndicator()->create([
                        'description' => $objectiveData['other_success_indicator'],
                    ]);
                }

                foreach ($objectiveData['activities'] as $activityData) {
                    $activity = $applicationObjective->activities()->create([
                        'activity_code' => $activityData['activity_code'],
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);


                    $activity->target()->create($activityData['target']);

                    foreach ($activityData['resources'] as $resourceData) {
                        $activity->resources()->create($resourceData);
                    }


                    foreach ($activityData['responsible_people'] as $personData) {
                        $activity->responsiblePeople()->create($personData);
                    }
                }
            }

            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');
            $ppmpTotal = 0;

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            $aopApplication->ppmpApplication()->update([
                'ppmp_total' => $ppmpTotal,
                'remarks' => $request->remarks ?? null,
            ]);


            $aopApplication->ppmpApplication->ppmpItems()->delete();

            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
                            $estimatedBudget = $resource->item->estimated_budget;
                            $totalAmount = $resource->quantity * $estimatedBudget;

                            $aopApplication->ppmpApplication->ppmpItems()->create([
                                'item_id' => $resource->item_id,
                                'total_quantity' => $resource->quantity,
                                'estimated_budget' => $estimatedBudget,
                                'total_amount' => $totalAmount,
                                'remarks' => null,
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'AOP Application updated successfully.']);
    }


    public function show($id)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments',
        ])->findOrFail($id);

        return new AopApplicationResource($aopApplication);
    }

    public function showTimeline($aopApplicationId)
    {
        $aopApplication = AopApplication::with('applicationTimeline')
            ->findOrFail($aopApplicationId);

        return ApplicationTimelineResource::make(
            $aopApplication->applicationTimeline
        );
    }
}
