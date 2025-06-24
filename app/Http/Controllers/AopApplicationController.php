<?php

namespace App\Http\Controllers;

use App\Helpers\TransactionLogHelper;
use App\Http\Requests\AopApplicationRequest;
use App\Http\Requests\ProcessAopRequest;
use App\Http\Resources\AopApplicationResource;
use App\Http\Resources\AopRemarksResource;
use App\Http\Resources\AopRequestResource;
use App\Http\Resources\AopResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Http\Resources\DesignationResource;
use App\Http\Resources\AssignedAreaResource;
use App\Models\Activity;
use App\Models\AopApplication;
use App\Models\ApplicationObjective;
use App\Models\AssignedArea;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\Item;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\PurchaseType;
use App\Models\Section;
use App\Models\SuccessIndicator;
use App\Models\Unit;
use App\Models\User;
use App\Models\ApplicationTimeline;
use App\Models\Log;
use App\Models\PpmpApplication;
use App\Models\Resource;
use App\Models\ResponsiblePerson;
use App\Services\ApprovalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use App\Services\AopVisibilityService;


class AopApplicationController extends Controller
{

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Get metadata for API responses
     *
     * @param string $method The method requesting metadata
     * @return array The metadata for the response
     */
    protected function getMetadata(string $method): array
    {
        $metadata = [
            'timestamp' => now(),
            'method' => $method
        ];

        if ($method === 'getAopApplications') {
            $metadata['statuses'] = ['pending', 'approved', 'returned'];
            $metadata['current_year'] = date('Y');
        }

        return $metadata;
    }

    public function index()
    {

        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.objective',
                'applicationObjectives.otherObjective',
                'applicationObjectives.successIndicator',
                'applicationObjectives.otherSuccessIndicator',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.resources.item',
                'applicationObjectives.activities.responsiblePeople.user',
                'applicationObjectives.activities.comments',
            ])
            ->get();

        return AopApplicationResource::collection($aopApplications);
    }

    public function getUserAopSummary(Request $request)
    {

        $user_id = $request->user()->id;
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();
        if (!$assignedArea) {
            return response()->json(['message' => 'User has no assigned area.'], 404);
        }

        $area = $assignedArea->findDetails();

        if (!$area || !isset($area['sector'], $area['details']['id'])) {
            return response()->json(['message' => 'Assigned area details are incomplete.'], 422);
        }


        $userAopApplication = AopApplication::where('sector', $area['sector'])
            ->where('sector_id', $area['details']['id'])
            ->orderByDesc('year')
            ->first();

        if (!$userAopApplication) {
            return response()->json(['data' => []]);
        }

        $summary = $this->getAopApplicationSummaryV2($userAopApplication->id);
        $aop = $this->getAOPV2($summary['aop_application_id']);

        $response = [
            'summary' => $summary,
            'aop' => $aop,



        ];

        return response()->json([
            'data' => $response,
            'message' => 'AOP Application retrieved successfully'
        ], Response::HTTP_OK);
    }

    // SSR function not for API
    public function getAOPV2($id)
    {
        $aop_application = AopApplication::with([
            'applicationObjectives' => function ($query) {
                $query->with([
                    'activities',
                    'activities.target',
                    'activities.resources',
                    'activities.resources.item',
                    'activities.resources.purchaseType',
                    'activities.responsiblePeople',
                    'activities.responsiblePeople.user',
                    'activities.responsiblePeople.division',
                    'activities.responsiblePeople.department',
                    'activities.responsiblePeople.section',
                    'activities.responsiblePeople.unit',
                    'activities.responsiblePeople.designation',
                    'activities.comments',
                    'objective',
                    'otherObjective',
                    'objective.successIndicators',
                    'objective.typeOfFunction',
                    'objective.typeOfFunction.objectives',
                    'objective.typeOfFunction.objectives.successIndicators',
                    'successIndicator',
                    'otherSuccessIndicator',
                ]);
            }
        ])->findOrFail($id);

        if (!$aop_application) {
            return null;
        }

        return new AopResource($aop_application);
    }


    public function getAopApplicationSummaryV2($aopApplicationId)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.successIndicator',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople',
            'applicationObjectives.activities.comments',
        ])->findOrFail($aopApplicationId);

        $objectives = $aopApplication->applicationObjectives;
        $activities = $objectives->flatMap->activities;

        $totalObjectives = $objectives->count();
        $totalActivities = $activities->count();
        $totalResources = $activities->flatMap->resources->count();
        $totalResponsiblePeople = $activities->flatMap->responsiblePeople;

        $totalComments = $activities->flatMap->comments->count();
        $totalSuccessIndicators = $objectives->pluck('successIndicator')->filter()->unique('id')->count();

        $gadRelatedActivities = $activities->filter(fn($act) => $act->is_gad_related);
        $notGadRelatedActivities = $activities->filter(fn($act) => !$act->is_gad_related);

        $totalGadRelated = $gadRelatedActivities->count();
        $totalNotGadRelated = $notGadRelatedActivities->count();

        $totalActivityCost = $activities->sum('cost');

        // Count distinct area combinations
        $totalAreas = $totalResponsiblePeople
            ->map(fn($p) => [
                'unit_id' => $p->unit_id,
                'department_id' => $p->department_id,
                'division_id' => $p->division_id,
                'section_id' => $p->section_id,
            ])
            ->filter(fn($area) => collect($area)->filter()->isNotEmpty()) // remove all-null combinations
            ->unique()
            ->count();

        // Count unique designation IDs
        $totalJobPositions = $totalResponsiblePeople->pluck('designation_id')->filter()->unique()->count();

        // Count unique user IDs
        $totalUsers = $totalResponsiblePeople->pluck('user_id')->filter()->unique()->count();

        return [
            'aop_application_id' => $aopApplication->id,
            'mission' => $aopApplication->mission,
            'year' => $aopApplication->year,
            'total_objectives' => $totalObjectives,
            'total_success_indicators' => $totalSuccessIndicators,
            'total_activities' => $totalActivities,
            'total_gad_related' => $totalGadRelated,
            'total_not_gad_related' => $totalNotGadRelated,
            'total_cost' => $totalActivityCost,
            'total_resources' => $totalResources,
            'total_comments' => $totalComments,
            'total_responsible_people' => $totalResponsiblePeople->count(),
            'total_areas' => $totalAreas,
            'total_job_positions' => $totalJobPositions,
            'total_users' => $totalUsers,
        ];
    }

    public function getAopApplicationSummary($aopApplicationId)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.successIndicator',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.responsiblePeople',
            'applicationObjectives.activities.comments',
        ])->findOrFail($aopApplicationId);

        $objectives = $aopApplication->applicationObjectives;
        $activities = $objectives->flatMap->activities;

        $totalObjectives = $objectives->count();
        $totalActivities = $activities->count();
        $totalResources = $activities->flatMap->resources->count();
        $totalResponsiblePeople = $activities->flatMap->responsiblePeople;

        $totalComments = $activities->flatMap->comments->count();
        $totalSuccessIndicators = $objectives->pluck('successIndicator')->filter()->unique('id')->count();

        $gadRelatedActivities = $activities->filter(fn($act) => $act->is_gad_related);
        $notGadRelatedActivities = $activities->filter(fn($act) => !$act->is_gad_related);

        $totalGadRelated = $gadRelatedActivities->count();
        $totalNotGadRelated = $notGadRelatedActivities->count();

        $totalActivityCost = $activities->sum('cost');

        // Count distinct area combinations
        $totalAreas = $totalResponsiblePeople
            ->map(fn($p) => [
                'unit_id' => $p->unit_id,
                'department_id' => $p->department_id,
                'division_id' => $p->division_id,
                'section_id' => $p->section_id,
            ])
            ->filter(fn($area) => collect($area)->filter()->isNotEmpty()) // remove all-null combinations
            ->unique()
            ->count();

        // Count unique designation IDs
        $totalJobPositions = $totalResponsiblePeople->pluck('designation_id')->filter()->unique()->count();

        // Count unique user IDs
        $totalUsers = $totalResponsiblePeople->pluck('user_id')->filter()->unique()->count();

        return response()->json([
            'aop_application_id' => $aopApplication->id,
            'mission' => $aopApplication->mission,
            'year' => $aopApplication->year,
            'total_objectives' => $totalObjectives,
            'total_success_indicators' => $totalSuccessIndicators,
            'total_activities' => $totalActivities,
            'total_gad_related' => $totalGadRelated,
            'total_not_gad_related' => $totalNotGadRelated,
            'total_cost' => $totalActivityCost,
            'total_resources' => $totalResources,
            'total_comments' => $totalComments,
            'total_responsible_people' => $totalResponsiblePeople->count(),
            'total_areas' => $totalAreas,
            'total_job_positions' => $totalJobPositions,
            'total_users' => $totalUsers,
        ]);
    }

    public function showUserTimeline(Request $request)
    {
        $user_id = $request->user()->id;

        // Get the user's assigned area
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();

        if (!$assignedArea) {
            return response()->json(['message' => 'User has no assigned area.'], 404);
        }

        $area = $assignedArea->findDetails();

        if (!$area || !isset($area['sector'], $area['details']['id'])) {
            return response()->json(['message' => 'Assigned area details are incomplete.'], 422);
        }

        // Find the AOP application for the user's sector, sector_id, and current year
        $aopApplication = AopApplication::with('applicationTimelines')
            ->where('sector', $area['sector'])
            ->where('sector_id', $area['details']['id'])
            ->orderByDesc('year')
            ->first();

        if (!$aopApplication) {
            return response()->json(['message' => 'No AOP application found for this year in your assigned area.'], 404);
        }

        return ApplicationTimelineResource::collection(
            $aopApplication->applicationTimelines
        );
    }
    // public function showTimeline($aopApplicationId)
    // {
    //     $aopApplication = AopApplication::with('applicationTimelines')
    //         ->findOrFail($aopApplicationId);

    //     return ApplicationTimelineResource::collection(
    //         $aopApplication->applicationTimelines
    //     );
    // }

    public function update(AopApplicationRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $aopApplication = AopApplication::with([
                'applicationObjectives.activities.resources.item',
                'ppmpApplication',
            ])->findOrFail($id);

            $user_id = $request->user()->id;
            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();
            $planningOfficer = Section::where('name', 'Planning Unit')->first();
            $planningOfficerId = optional($planningOfficer)->head_id;
            $curr_user = User::find($user_id);
            $curr_user_authorization_pin = $curr_user->authorization_pin;

            if ($curr_user_authorization_pin !== $request->authorization_pin) {
                return response()->json([
                    'message' => 'Invalid Authorization Pin'
                ], Response::HTTP_BAD_REQUEST);
            }

            $aopApplication->update($request->only([
                'mission',
                'status',
                'has_discussed',
                'remarks',
            ]));

            // foreach ($aopApplication->applicationObjectives as $objective) {
            //     foreach ($objective->activities as $activity) {
            //         $activity->target()->delete();
            //         $activity->resources()->delete();
            //         $activity->responsiblePeople()->delete();
            //     }
            //     $objective->activities()->delete();
            //     $objective->otherObjective()->delete();
            //     $objective->otherSuccessIndicator()->delete();
            // }

            // $aopApplication->applicationObjectives()->delete();

            // foreach ($request->application_objectives as $objectiveData) {
            //     $applicationObjective = $aopApplication->applicationObjectives()->create([
            //         'objective_id' => $objectiveData['objective_id'],
            //         'success_indicator_id' => $objectiveData['success_indicator_id'],
            //         'objective_code' => $objectiveData['objective_code'] ?? null,
            //     ]);

            //     if (($applicationObjective->objective->description ?? null) === 'Others' && isset($objectiveData['others_objective'])) {
            //         $applicationObjective->othersObjective()->create([
            //             'description' => $objectiveData['others_objective'],
            //         ]);
            //     }

            //     if (($applicationObjective->successIndicator->description ?? null) === 'Others' && isset($objectiveData['other_success_indicator'])) {
            //         $applicationObjective->otherSuccessIndicator()->create([
            //             'description' => $objectiveData['other_success_indicator'],
            //         ]);
            //     }

            //     foreach ($objectiveData['activities'] as $activityData) {
            //         $activity = $applicationObjective->activities()->create([
            //             'activity_code' => $this->generateUniqueActivityCode(),
            //             'name' => $activityData['name'],
            //             'is_gad_related' => $activityData['is_gad_related'],
            //             'cost' => $activityData['cost'],
            //             'start_month' => $activityData['start_month'],
            //             'end_month' => $activityData['end_month'],
            //         ]);

            //         $activity->target()->create($activityData['target']);

            //         foreach ($activityData['resources'] as $resourceData) {
            //             $activity->resources()->create($resourceData);
            //         }

            //         foreach ($activityData['responsible_people'] as $personData) {
            //             $activity->responsiblePeople()->create($personData);
            //         }
            //     }
            // }

            // Log::create([
            //     'aop_application_id' => $aopApplication->id,
            //     'ppmp_application_id' => null,
            //     'action' => "Update Aop",
            //     'action_by' => $user_id,
            // ]);

            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');
            $ppmpTotal = 0;

            // foreach ($aopApplication->applicationObjectives as $objective) {
            //     foreach ($objective->activities as $activity) {
            //         foreach ($activity->resources as $resource) {
            //             if ($resource->purchase_type_id === $procurablePurchaseTypeId) {
            //                 $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
            //             }
            //         }
            //     }
            // }

            $aopApplication->ppmpApplication()->update([
                // 'ppmp_total' => $ppmpTotal,
                'remarks' => $request->remarks ?? null,
            ]);

            // $ppmpApplication = $aopApplication->ppmpApplication;
            // $ppmpApplication->ppmpItems()->delete();

            // $mergedItems = [];

            // foreach ($aopApplication->applicationObjectives as $objective) {
            //     foreach ($objective->activities as $activity) {
            //         foreach ($activity->resources as $resource) {
            //             if (
            //                 $resource->purchase_type_id === $procurablePurchaseTypeId &&
            //                 $resource->item
            //             ) {
            //                 $itemId = $resource->item_id;
            //                 $estimatedBudget = $resource->item->estimated_budget;
            //                 $quantity = $resource->quantity;
            //                 $totalAmount = $quantity * $estimatedBudget;
            //                 $expenseClass = $resource->expense_class;

            //                 // Merge by item ID and expense class if needed
            //                 $key = $itemId . '-' . $expenseClass;

            //                 if (isset($mergedItems[$key])) {
            //                     $mergedItems[$key]['total_quantity'] += $quantity;
            //                     $mergedItems[$key]['total_amount'] += $totalAmount;
            //                 } else {
            //                     $mergedItems[$key] = [
            //                         'item_id' => $itemId,
            //                         'total_quantity' => $quantity,
            //                         'estimated_budget' => $estimatedBudget,
            //                         'total_amount' => $totalAmount,
            //                         'expense_class' => $expenseClass,
            //                         'remarks' => null,
            //                     ];
            //                 }
            //             }
            //         }
            //     }
            // }

            // Insert merged items
            // foreach ($mergedItems as $itemData) {
            //     $ppmpApplication->ppmpItems()->create($itemData);
            // }

            // Log::create([
            //     'aop_application_id' => null,
            //     'ppmp_application_id' => $ppmpApplication->id,
            //     'action' => "Update Ppmp",
            //     'action_by' => $user_id,
            // ]);

            // Approval flow
            $aop_user = User::find($aopApplication->user_id);
            $approval_service = new ApprovalService($this->notificationService);
            $timeline = $approval_service->createInitialApplicationTimeline(
                $aopApplication,
                $curr_user,
                $request->remarks
            );

            if (!$timeline) {
                DB::rollBack();
                return response()->json([
                    'message' => 'AOP application timeline not created',
                ], Response::HTTP_BAD_REQUEST);
            }

            DB::commit();
            return response()->json(['message' => 'AOP Application updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update AOP Application.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    protected function generateUniqueActivityCode(): string
    {
        do {
            $code = 'ACT-' . strtoupper(Str::random(6)); // e.g. ACT-X8Y9Z1
        } while (\App\Models\Activity::where('activity_code', $code)->exists());

        return $code;
    }

    public function store(AopApplicationRequest $request)
    {

        $validatedData = $request->validated();
        if ($request->status !== 'draft') {
            $curr_user = User::find($request->user()->id);
            $curr_user_authorization_pin = $curr_user->authorization_pin;

            if ($curr_user_authorization_pin !== $request->authorization_pin) {
                return response()->json([
                    'message' => 'Invalid Authorization Pin'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        DB::beginTransaction();

        try {

            $user_id = $request->user()->id;
            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();
            $existingAop = AopApplication::where('sector', $area['sector'])
                ->where('sector_id', $area['details']['id'])
                ->first();

            if ($existingAop) {
                return response()->json([
                    'message' => 'You already have an AOP application in your area.',
                ], 200);
            }

            switch ($area['sector']) {
                case 'Division':
                    $division = Division::where('name', $area['details']['name'])->first();
                    $divisionChiefId = $division->head_id;
                    break;
                case 'Department':
                    $department = Department::where('name', $area['details']['name'])->first();
                    $division = Division::where('id', $department->division_id)->first();
                    $divisionChiefId = $division->head_id;
                    break;
                case 'Section':
                    $section = Section::where('name', $area['details']['name'])->first();
                    if ($section?->department_id) {
                        $department = Department::find($section->department_id);
                        $division = Division::find($department?->division_id);
                    } else {
                        $division = Division::find($section?->division_id);
                    }

                    $divisionChiefId = $division?->head_id ?? null;

                    break;
                case 'Unit':
                    $unit = Unit::where('name', $area['details']['name'])->first();
                    $section = Section::where('id', $unit->section_id)->first();

                    if ($section?->department_id) {
                        $department = Department::find($section->department_id);
                        $division = Division::find($department?->division_id);
                    } else {

                        $division = Division::find($section?->division_id);
                    }
                    $divisionChiefId = $division?->head_id ?? null;
                    break;
            }

            $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
            $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

            if (is_null($mccChiefId)) {
                return response()->json(['message' => 'Medical Center Chief not found.'], 404);
            }


            $planningOfficer = Section::where('name', 'Planning Unit')->first();
            $planningOfficerId = optional($planningOfficer)->head_id;


            if (is_null($planningOfficerId)) {
                return response()->json(['message' => 'Planning Officer not found.'], 404);
            }

            $budgetOfficer = Section::where('name', 'FS: Budget Section')->first();
            $budgetOfficerId = optional($budgetOfficer)->head_id;


            if (is_null($budgetOfficerId)) {
                return response()->json(['message' => 'Budget Officer not found.'], 404);
            }



            // Create AOP Application
            $aopApplication = AopApplication::create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'mcc_chief_id' => $mccChiefId,
                'planning_officer_id' => $planningOfficerId,
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission']  ?? null,
                'status' => $validatedData['status'],
                'sector' => $area['sector'],
                'sector_id' => $area['details']['id'],
                'has_discussed' => $validatedData['has_discussed']  ?? null,
                'remarks' => $validatedData['remarks'] ?? null,
                'year' => now()->year + 1,
            ]);

            if ($request->status !== 'draft') {
                $ppmpApplication = $aopApplication->ppmpApplication()->create([
                    'user_id' => $user_id,
                    'division_chief_id' => $divisionChiefId,
                    'budget_officer_id' => $budgetOfficerId,
                    'planning_officer_id' => $planningOfficerId,
                    'ppmp_application_uuid' => Str::uuid(),
                    'year' => now()->addYear()->year,
                    'status' => $validatedData['status'],
                ]);
            }

            if (!empty($validatedData['application_objectives'])) {
                foreach ($validatedData['application_objectives'] as $objectiveData) {
                    $applicationObjective = $aopApplication->applicationObjectives()->create([
                        'objective_id' => $objectiveData['objective_id'],
                        'success_indicator_id' => $objectiveData['success_indicator_id'],
                    ]);


                    if ($applicationObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                        $applicationObjective->otherObjective()->create([
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
                    if (!empty($objectiveData['activities'])) {
                        foreach ($objectiveData['activities'] as $activityData) {
                            $activity = $applicationObjective->activities()->create([
                                'activity_code' => $this->generateUniqueActivityCode(),
                                'name' => $activityData['name'],
                                'is_gad_related' => $activityData['is_gad_related'],
                                'cost' => $activityData['cost'],
                                'start_month' => $activityData['start_month'],
                                'end_month' => $activityData['end_month'],
                            ]);

                            if (!empty($activityData['target']) && is_array($activityData['target'])) {
                                $activity->target()->create($activityData['target']);
                            }

                            if (!empty($activityData['resources']) && is_array($activityData['resources'])) {
                                $activity->resources()->createMany($activityData['resources']);
                            }

                            if (!empty($activityData['responsible_people']) && is_array($activityData['responsible_people'])) {
                                $activity->responsiblePeople()->createMany($activityData['responsible_people']);
                            }

                            if ($request->status !== 'draft') {
                                $ppmp_item = null;
                                foreach ($activityData['resources'] as $item) {
                                    $items = Item::find($item['item_id']);
                                    $ppmp_item = PpmpItem::create([
                                        'ppmp_application_id' => $ppmpApplication->id,
                                        'item_id' => $items->id,
                                        'estimated_budget' => $items->estimated_budget,
                                        'expense_class' => $item['expense_class'],
                                    ]);

                                    if ($ppmp_item) {
                                        $activity_ppmp_item = $activity->ppmpItems()
                                            ->where('activity_id', $activity->id)
                                            ->where('ppmp_item_id', $ppmp_item->id)
                                            ->first();

                                        if ($activity_ppmp_item === null) {
                                            $activity->ppmpItems()->attach($ppmp_item->id);

                                            for ($month = 1; $month <= 12; $month++) {
                                                PpmpSchedule::create([
                                                    'ppmp_item_id' => $ppmp_item->id,
                                                    'month' => $month,
                                                    'year' => now()->addYear()->year,
                                                    'quantity' => 0
                                                ]);
                                            }
                                        }
                                    } else {
                                        DB::rollBack();
                                        return response()->json([
                                            'message' => 'Failed to create PPMP item for activity.',
                                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($request->status !== 'draft') {
                $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');

                $ppmpTotal = 0;
                foreach ($aopApplication->applicationObjectives as $objective) {
                    foreach ($objective->activities as $activity) {
                        foreach ($activity->resources as $resource) {
                            if (
                                $resource->purchase_type_id === $procurablePurchaseTypeId &&
                                $resource->item
                            ) {
                                $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                            }
                        }
                    }
                }

                $ppmpApplication->update(['ppmp_total' => $ppmpTotal]);

                Log::create([
                    'aop_application_id' => null,
                    'ppmp_application_id' => $ppmpApplication->id,
                    'action' => "Create Ppmp",
                    'action_by' => $user_id,
                ]);
            }

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'ppmp_application_id' => null,
                'action' => "Create Aop",
                'action_by' => $user_id,
            ]);




            if ($request->status !== 'draft') {

                // Get the aop user and its area
                $aop_user = User::find($aopApplication->user_id);
                $aop_user_assigned_area = $aop_user->assignedArea;
                $aop_user_authorization_pin = $aop_user->authorization_pin;

                // Get the approval service
                $approval_service = new ApprovalService($this->notificationService);

                // Create an initial timeline entry using the specialized method
                $aop_application_timeline = $approval_service->createInitialApplicationTimeline(
                    $aopApplication,
                    $curr_user,
                    $request->remarks
                );

                if (!$aop_application_timeline || (is_array($aop_application_timeline) && !($aop_application_timeline['success'] ?? true))) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create AOP application timeline',
                        'details' => is_array($aop_application_timeline) ? ($aop_application_timeline['error'] ?? 'Unknown error') : 'Unknown error'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            DB::commit();

            return response()->json(['message' => 'AOP Application created successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(), // optional: useful for debugging
            ], 500);
        }
    }


    public function updateExisting(AopApplicationRequest $request, $id)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $aopApplication = AopApplication::with('applicationObjectives.activities')->findOrFail($id);

            if ($validatedData['status'] !== 'draft') {
                $curr_user = User::find($request->user()->id);
                if ($curr_user->authorization_pin !== $request->authorization_pin) {
                    return response()->json(['message' => 'Invalid Authorization Pin'], 400);
                }
            }

            $aopApplication->update([
                'mission' => $validatedData['mission'] ?? $aopApplication->mission,
                'status' => $validatedData['status'],
                'has_discussed' => $validatedData['has_discussed'] ?? $aopApplication->has_discussed,
                'remarks' => $validatedData['remarks'] ?? $aopApplication->remarks,
            ]);

            // Delete existing related data
            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    $activity->ppmpItems()->detach();
                    $activity->resources()->delete();
                    $activity->responsiblePeople()->delete();
                    $activity->target()->delete();
                    $activity->delete();
                }
                $objective->otherObjective()?->delete();
                $objective->otherSuccessIndicator()?->delete();
                $objective->delete();
            }

            // Recreate related data
            if (!empty($validatedData['application_objectives'])) {
                foreach ($validatedData['application_objectives'] as $objectiveData) {
                    $applicationObjective = $aopApplication->applicationObjectives()->create([
                        'objective_id' => $objectiveData['objective_id'],
                        'success_indicator_id' => $objectiveData['success_indicator_id'],
                    ]);

                    if ($applicationObjective->objective->description === 'Others' && isset($objectiveData['others_objective'])) {
                        $applicationObjective->otherObjective()->create([
                            'description' => $objectiveData['others_objective'],
                        ]);
                    }

                    $successIndicator = SuccessIndicator::find($objectiveData['success_indicator_id']);
                    if ($successIndicator && $successIndicator->description === 'Others' && isset($objectiveData['other_success_indicator'])) {
                        $applicationObjective->otherSuccessIndicator()->create([
                            'description' => $objectiveData['other_success_indicator'],
                        ]);
                    }

                    foreach ($objectiveData['activities'] ?? [] as $activityData) {
                        $activity = $applicationObjective->activities()->create([
                            'activity_code' => $this->generateUniqueActivityCode(),
                            'name' => $activityData['name'],
                            'is_gad_related' => $activityData['is_gad_related'],
                            'cost' => $activityData['cost'],
                            'start_month' => $activityData['start_month'],
                            'end_month' => $activityData['end_month'],
                        ]);

                        $activity->target()->create($activityData['target'] ?? []);

                        if (!empty($activityData['resources'])) {
                            $activity->resources()->createMany($activityData['resources']);

                            $ppmpApplication = $aopApplication->ppmpApplication;

                            foreach ($activityData['resources'] as $item) {
                                $itemModel = Item::find($item['item_id']);
                                $ppmpItem = PpmpItem::create([
                                    'ppmp_application_id' => $ppmpApplication->id,
                                    'item_id' => $itemModel->id,
                                    'estimated_budget' => $itemModel->estimated_budget,
                                    'expense_class' => $item['expense_class'],
                                ]);

                                $activity->ppmpItems()->attach($ppmpItem->id);

                                for ($month = 1; $month <= 12; $month++) {
                                    PpmpSchedule::create([
                                        'ppmp_item_id' => $ppmpItem->id,
                                        'month' => $month,
                                        'year' => now()->addYear()->year,
                                        'quantity' => 0,
                                    ]);
                                }
                            }
                        }

                        if (!empty($activityData['responsible_people'])) {
                            $activity->responsiblePeople()->createMany($activityData['responsible_people']);
                        }
                    }
                }
            }

            // Recalculate PPMP total
            $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');
            $ppmpTotal = 0;
            foreach ($aopApplication->applicationObjectives as $objective) {
                foreach ($objective->activities as $activity) {
                    foreach ($activity->resources as $resource) {
                        if (
                            $resource->purchase_type_id === $procurablePurchaseTypeId &&
                            $resource->item
                        ) {
                            $ppmpTotal += $resource->quantity * $resource->item->estimated_budget;
                        }
                    }
                }
            }

            $aopApplication->ppmpApplication->update(['ppmp_total' => $ppmpTotal]);

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'action' => 'Update Aop',
                'action_by' => $request->user()->id,
            ]);

            if ($validatedData['status'] !== 'draft') {
                $approvalService = new ApprovalService($this->notificationService);
                $timelineResult = $approvalService->createInitialApplicationTimeline(
                    $aopApplication,
                    $request->user(),
                    $request->remarks
                );

                if (!$timelineResult || (is_array($timelineResult) && !($timelineResult['success'] ?? true))) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create AOP application timeline',
                        'details' => is_array($timelineResult) ? ($timelineResult['error'] ?? 'Unknown error') : 'Unknown error'
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            DB::commit();
            return response()->json(['message' => 'AOP Application updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(), // Optional for debugging
            ], 500);
        }
    }


    public function show($id)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments',
        ])->findOrFail($id);

        foreach ($aopApplication->applicationObjectives as $objective) {
            foreach ($objective->activities as $activity) {
                foreach ($activity->responsiblePeople as $responsiblePerson) {
                    $responsiblePerson->resolved_name = $this->getResponsiblePersonName($responsiblePerson);
                }
            }
        }

        return new AopApplicationResource($aopApplication);
    }

    public function showWithComments($id)
    {
        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.comments', // Include activity comments
        ])->findOrFail($id);

        return new AopApplicationResource($aopApplication);
    }

    public function getUsersWithDesignation(Request $request)
    {
        // $userId = $request->user()->id;
        $userId = 2;
        $assignedArea = AssignedArea::where('user_id', $userId)->first();

        if (!$assignedArea) {
            return response()->json(['message' => 'User has no assigned area.'], 404);
        }

        $areaDetails = $assignedArea->findDetails();

        $sector = $areaDetails['sector'];
        $sectorId = $areaDetails['details']->resource->id;

        $sectorColumn = strtolower($sector) . '_id';

        // Get the sector model and head
        $sectorModel = match ($sector) {
            'Division' => Division::find($sectorId),
            'Department' => Department::find($sectorId),
            'Section' => Section::find($sectorId),
            'Unit' => Unit::find($sectorId),
            default => null
        };

        $sectorHead = $sectorModel?->head;

        // Get users in same area
        $users = User::with(['assignedArea.designation'])
            ->where('id', '!=', 1)
            ->whereHas('assignedArea', fn($q) => $q->where($sectorColumn, $sectorId))
            ->get()
            ->map(function ($user) {
                $assignedArea = $user->assignedArea;
                $area = $assignedArea?->findDetails();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => $user->name,
                    'designation' => optional($assignedArea?->designation)->name ?? 'No Designation',
                    'sector' => $area['sector'] ?? 'Unassigned',
                    'sector_id' => $area['details']['id'] ?? null,
                    'sector_name' => $area['details']['name'] ?? null,
                    'is_head' => false,
                ];
            });

        // Add the sector head if not already included
        if ($sectorHead && !$users->contains('id', $sectorHead->id)) {
            $headArea = $sectorHead->assignedArea;
            $headDetails = $headArea?->findDetails();

            $users->prepend([
                'id' => $sectorHead->id,
                'name' => $sectorHead->name,
                'label' => $sectorHead->name,
                'designation' => optional($headArea?->designation)->name ?? 'No Designation',
                'sector' => $headDetails['sector'] ?? 'Unassigned',
                'sector_id' => $headDetails['details']['id'] ?? null,
                'sector_name' => $headDetails['details']['name'] ?? null,
                'is_head' => true,
            ]);
        }

        return response()->json($users->values());
    }

    public function getAllDesignations()
    {
        $designations = Designation::all();

        return DesignationResource::collection($designations);
    }

    public function getAllArea()
    {
        $divisions = Division::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'division';
            $item->label = $item->name;
            return $item;
        });

        $departments = Department::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'department';
            $item->label = $item->name;
            return $item;
        });

        $sections = Section::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'section';
            $item->label = $item->name;
            return $item;
        });

        $units = Unit::select('id', 'name')->get()->map(function ($item) {
            $item->type = 'unit';
            $item->label = $item->name;
            return $item;
        });

        $all = $divisions
            ->concat($departments)
            ->concat($sections)
            ->concat($units)
            ->values();

        return response()->json($all);
    }


    public function aopRequests(Request $request): JsonResponse
    {
        $visibilityService = new AopVisibilityService();
        $filters = $request->only(['status', 'year', 'search']);

        $aopApplications = $visibilityService->getAopApplications($request->user(), $filters)
            ->orderBy('updated_at', 'desc')
            ->get();

        $metadata = $this->getMetadata('getAopApplications');

        return response()->json([
            'metadata' => $metadata,
            'data' => AopRequestResource::collection($aopApplications),
        ]);
    }


    /**
     * Process an AOP application request through the approval workflow
     *
     * @param ProcessAopRequest $request The incoming request
     * @return \Illuminate\Http\JsonResponse JSON response
     * @throws \Exception
     */
    public function processAopRequest(ProcessAopRequest $request): JsonResponse
    {
        $aop_application = AopApplication::with([
            'applicationObjectives',
            'applicationTimelines',
            'user'
        ])
            ->where('id', $request->aop_application_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$aop_application) {
            return response()->json([
                'message' => 'AOP Application not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Get the current user and its area
        $curr_user = User::find($request->user()->id);
        $curr_user_assigned_area = $curr_user->assignedArea;
        $curr_user_authorization_pin = $curr_user->authorization_pin;

        // Get the aop user and its area
        $aop_user = User::find($aop_application->user_id);

        $aop_user_assigned_area = $aop_user->assignedArea;


        if ($curr_user_authorization_pin !== $request->authorization_pin) {
            return response()->json([
                'message' => 'Invalid Authorization Pin'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Use ApprovalService to process the request
        $approval_service = new ApprovalService($this->notificationService);

        // Create a timeline entry using the service
        $aop_application_timeline = $approval_service->createApplicationTimeline(
            $aop_application,
            $curr_user,
            $aop_user,
            $request->status,
            $request->remarks
        );

        if (!$aop_application_timeline) {
            return response()->json([
                'message' => 'AOP application timeline not created',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Update the AOP application status based on the workflow stage
        $statusMap = [
            'approved' => 'approved',
            'returned' => 'returned',
            // Add other status mappings as needed
        ];

        if (isset($statusMap[$request->status])) {
            $aop_application->status = $statusMap[$request->status];
            $aop_application->save();
        }

        // Get the current user information
        $user_info = $curr_user_assigned_area->user;

        // Get the next office information based on the updated application's current area
        // This is set in the workflow service during timeline creation
        $next_area_info = null;

        $application_timeline = ApplicationTimeline::where('aop_application_id', $aop_application->id)->latest()->first();

        // Notifications are now handled in ApprovalService

        // Build detailed success response
        $current_area_info = new AssignedAreaResource($curr_user_assigned_area);

        $status_text = ucfirst($request->status);

        $response_message = "AOP application {$status_text}";
        if ($request->status === 'approved') {
            $response_message .= " and forwarded to next step";
        } elseif ($request->status === 'returned') {
            $response_message .= " and sent back for revision";
        }

        return response()->json([
            'success' => true,
            'message' => $response_message,
            'data' => [
                'timeline' => $aop_application_timeline,
                'status_details' => [
                    'status' => $aop_application->status,
                    'current_area_info' => $current_area_info,
                    'next_area_info' => $next_area_info,
                    'date_updated' => now()->format('Y-m-d H:i:s'),
                    'remarks' => $request->remarks
                ]
            ]
        ], Response::HTTP_OK);
    }
    public function export($id)
    {

        $aopApplication = AopApplication::find($id);

        if (!$aopApplication) {
            return response()->json(['message' => 'AOP Application not found'], 404);
        }

        $user_id = $aopApplication->user_id;
        $assignedArea = AssignedArea::where('user_id', $user_id)->first();
        $area = $assignedArea->findDetails();
        $userArea = $area['details']['name'];
        switch ($area['sector']) {
            case 'Division':
                $division = Division::where('name', $area['details']['name'])->first();
                $divisionChiefId = $division->head_id;
                $headId = $division->head_id;
                break;
            case 'Department':
                $department = Department::where('name', $area['details']['name'])->first();
                $headId = $department->head_id;
                $division = Division::where('id', $department->division_id)->first();
                $divisionChiefId = $division->head_id;
                break;
            case 'Section':
                $section = Section::where('name', $area['details']['name'])->first();
                $headId = $section->head_id;
                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {
                    $division = Division::find($section?->division_id);
                }

                $divisionChiefId = $division?->head_id ?? null;
                break;
            case 'Unit':
                $unit = Unit::where('name', $area['details']['name'])->first();
                $headId = $unit->head_id;
                $section = Section::where('id', $unit->section_id)->first();

                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {

                    $division = Division::find($section?->division_id);
                }
                $divisionChiefId = $division?->head_id ?? null;
                break;
        }

        $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
        $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

        $planningOfficer = Section::where('name', 'Planning Unit')->first();
        $planningOfficerId = optional($planningOfficer)->head_id;

        $head = \App\Models\User::find($headId);
        $preparedByName = $head?->name ?? 'N/A';
        $divisionHead = \App\Models\User::find($divisionChiefId);
        $notedBy = $divisionHead?->name ?? 'N/A';
        $mccChief = \App\Models\User::find($mccChiefId);
        $ApprovedBy = $mccChief?->name ?? 'N/A';
        $planning = \App\Models\User::find($planningOfficerId);
        $ReviewedBy = $planning?->name ?? 'N/A';

        $aopApplication = AopApplication::with([
            'applicationObjectives.objective',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.otherObjective',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherSuccessIndicator',
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationTimelines',
        ])->findOrFail($id);

        $getResponsiblePersonName = function ($responsiblePerson) {
            if ($responsiblePerson->user_id) {
                return $responsiblePerson->user->name;
            }

            if ($responsiblePerson->division_id) {
                return $responsiblePerson->division->name;
            }
            if ($responsiblePerson->department_id) {
                return $responsiblePerson->department->name;
            }
            if ($responsiblePerson->section_id) {
                return $responsiblePerson->section->name;
            }
            if ($responsiblePerson->unit_id) {
                return $responsiblePerson->unit->name;
            }

            return 'Unknown';
        };


        // Prepare the objectives and activities data
        $objectives = $aopApplication->applicationObjectives->map(function ($objective) use ($getResponsiblePersonName, $aopApplication) {
            return $objective->activities->map(function ($activity) use ($objective, $getResponsiblePersonName, $aopApplication) {
                // Process resources
                $resources = $activity->resources->map(function ($resource) {
                    $quantity = $resource->quantity ?? '';
                    $itemName = $resource->item->name ?? '';
                    return "{$quantity} {$itemName}";
                })->filter()->implode("\n");

                // Process responsible people
                $responsiblePeople = $activity->responsiblePeople->map(function ($responsible) use ($getResponsiblePersonName) {
                    return $getResponsiblePersonName($responsible); // Get the name
                })->filter()->implode("\n");

                $expenseClasses = $activity->resources->map(function ($resource) {
                    return $resource->expense_class ?? '';
                })->filter()->implode("\n");

                return [
                    'mission' => $aopApplication->mission,
                    'type_of_function' => $objective->objective->typeOfFunction->type ?? '',
                    'objective' => ($objective->objective->description === 'Others')
                        ? ($objective->otherObjective->description ?? '')
                        : ($objective->objective->description ?? ''),

                    'success_indicator' => ($objective->successIndicator->description === 'Others')
                        ? ($objective->otherSuccessIndicator->description ?? '')
                        : ($objective->successIndicator->description ?? ''),
                    'activity_name' => $activity->name,
                    'start_month' => $activity->start_month ? \Carbon\Carbon::parse($activity->start_month)->format('F') : '',
                    'end_month' => $activity->end_month ? \Carbon\Carbon::parse($activity->end_month)->format('F') : '',
                    'target_q1' => $activity->target->first_quarter ?? '',
                    'target_q2' => $activity->target->second_quarter ?? '',
                    'target_q3' => $activity->target->third_quarter ?? '',
                    'target_q4' => $activity->target->fourth_quarter ?? '',
                    'resources' => $resources,
                    'cost' => $activity->cost ?? 0,
                    'expense_class' => $expenseClasses,
                    'responsible_people' => $responsiblePeople,
                    'is_gad_related' => $activity->is_gad_related,
                ];
            });
        });



        // Load template
        $templatePath = storage_path('app/template/operational_plan.xlsx');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B8', $aopApplication->mission);
        $sheet->setCellValue('D7', $area['details']['name']);

        $defaultTableStart = 14; // Starting row of the table
        $defaultTableRows = 30;
        $totalDataRows = $objectives->flatten(1)->count();

        if ($totalDataRows > $defaultTableRows) {
            $rowsToAdd = $totalDataRows - $defaultTableRows;

            // Insert new rows after the last default row (row 14 + 30 = 44)
            $insertPosition = $defaultTableStart + $defaultTableRows;

            $sheet->insertNewRowBefore($insertPosition + 1, $rowsToAdd);

            // Optional: Copy style from the last default row to new rows
            $templateRow = $insertPosition;
            for ($i = 1; $i <= $rowsToAdd; $i++) {
                $sourceRow = $templateRow;
                $targetRow = $templateRow + $i;

                for ($col = 'A'; $col <= 'O'; $col++) {
                    $style = $sheet->getStyle("{$col}{$sourceRow}");
                    $sheet->duplicateStyle($style, "{$col}{$targetRow}");
                }
            }
        }

        // Start populating from row 14
        $row = 14;
        foreach ($objectives as $objectiveActivities) {
            foreach ($objectiveActivities as $item) {
                // Set the data in the cells
                $sheet->setCellValue("A{$row}", $item['type_of_function']);
                $sheet->setCellValue("B{$row}", $item['objective']);
                $sheet->setCellValue("C{$row}", $item['success_indicator']);
                $sheet->setCellValue("D{$row}", $item['activity_name']);
                $sheet->setCellValue("E{$row}", $item['start_month']);
                $sheet->setCellValue("F{$row}", $item['end_month']);
                $sheet->setCellValue("G{$row}", $item['target_q1']);
                $sheet->setCellValue("G{$row}", $item['target_q1']);
                $sheet->setCellValue("H{$row}", $item['target_q2']);
                $sheet->setCellValue("I{$row}", $item['target_q3']);
                $sheet->setCellValue("J{$row}", $item['target_q4']);
                $sheet->setCellValue("K{$row}", $item['resources']);
                $sheet->setCellValue("L{$row}", $item['cost']);
                $sheet->setCellValue("M{$row}", $item['expense_class']);
                $sheet->setCellValue("N{$row}", $item['responsible_people']);
                $sheet->setCellValue("O{$row}", $item['is_gad_related']);
                $row++;
            }
        }

        $lastDataRow = $row - 1; // Last populated row with actual data

        // Find the start of the footer/signature section
        $footerStartRow = null;
        $maxScanRow = $sheet->getHighestRow();

        for ($i = $lastDataRow + 1; $i <= $maxScanRow; $i++) {
            $cellB = $sheet->getCell("B{$i}")->getValue();
            $cellD = $sheet->getCell("D{$i}")->getValue();
            $cellK = $sheet->getCell("K{$i}")->getValue();
            $cellM = $sheet->getCell("M{$i}")->getValue();

            if (
                stripos($cellB, 'Prepared by') !== false ||
                stripos($cellD, 'Noted by') !== false ||
                stripos($cellK, 'Reviewed by') !== false ||
                stripos($cellM, 'Approved by') !== false
            ) {
                $footerStartRow = $i;
                break;
            }
        }

        // Delete empty rows between data and footer, but keep 4 clean rows
        $rowsToKeep = 4;
        if ($footerStartRow && $footerStartRow > $lastDataRow + $rowsToKeep) {
            $numRowsToDelete = $footerStartRow - $lastDataRow - $rowsToKeep;
            $sheet->removeRow($lastDataRow + 1, $numRowsToDelete);
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("B{$i}")->getValue();
            if (stripos($cellValue, 'Prepared by') !== false) {
                $sheet->setCellValue("B" . ($i + 2), $preparedByName);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("D{$i}")->getValue();
            if (stripos($cellValue, 'Noted by:') !== false) {
                $sheet->setCellValue("D" . ($i + 2), $notedBy);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("K{$i}")->getValue();
            if (stripos($cellValue, 'Reviewed by:') !== false) {
                $sheet->setCellValue("K" . ($i + 2), $ReviewedBy);
                break;
            }
        }

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellValue = $sheet->getCell("M{$i}")->getValue();
            if (stripos($cellValue, 'Approved by:') !== false) {
                $sheet->setCellValue("M" . ($i + 2), $ApprovedBy);
                break;
            }
        }

        $lastRow = $row - 1;
        $sheet->getStyle("G14:J{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle("L14:L{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        for ($i = 1; $i <= $sheet->getHighestRow(); $i++) {
            $cellB = $sheet->getCell("B{$i}")->getValue();
            $cellD = $sheet->getCell("D{$i}")->getValue();
            $cellK = $sheet->getCell("K{$i}")->getValue();
            $cellM = $sheet->getCell("M{$i}")->getValue();

            // Prepared by (column B)
            if (stripos($cellB, 'Prepared by') !== false) {
                $sheet->setCellValue("B" . ($i + 2), $preparedByName);

                $preparedByTimeline = $aopApplication->applicationTimelines()
                    ->where('user_id', $user_id)
                    ->orderByDesc('created_at')
                    ->first();

                $formattedDate = $preparedByTimeline
                    ? \Carbon\Carbon::parse($preparedByTimeline->created_at)->format('F j, Y')
                    : '____________';

                $richText = new RichText();
                $richText->createText('DATE: ');
                $richText->createTextRun($formattedDate)->getFont()->setUnderline(true);

                $sheet->setCellValue("B" . ($i + 5), $richText);
            }

            // Noted by (column D)
            if (stripos($cellD, 'Noted by') !== false) {
                $sheet->setCellValue("D" . ($i + 2), $notedBy);

                $notedByTimeline = $aopApplication->applicationTimelines()
                    ->where('user_id', $divisionChiefId)
                    ->orderByDesc('created_at')
                    ->first();

                $formattedDate = $notedByTimeline
                    ? \Carbon\Carbon::parse($notedByTimeline->created_at)->format('F j, Y')
                    : '____________';

                $richText = new RichText();
                $richText->createText('DATE: ');
                $richText->createTextRun($formattedDate)->getFont()->setUnderline(true);

                $sheet->setCellValue("D" . ($i + 5), $richText);
            }

            // Reviewed by (column K)
            if (stripos($cellK, 'Reviewed by') !== false) {
                $sheet->setCellValue("K" . ($i + 2), $ReviewedBy);

                $reviewedByTimeline = $aopApplication->applicationTimelines()
                    ->where('user_id', $planningOfficerId)
                    ->orderByDesc('created_at')
                    ->first();

                $formattedDate = $reviewedByTimeline
                    ? \Carbon\Carbon::parse($reviewedByTimeline->created_at)->format('F j, Y')
                    : '____________';

                $richText = new RichText();
                $richText->createText('DATE: ');
                $richText->createTextRun($formattedDate)->getFont()->setUnderline(true);

                $sheet->setCellValue("K" . ($i + 5), $richText);
            }

            // Approved by (column M)
            if (stripos($cellM, 'Approved by') !== false) {
                $sheet->setCellValue("M" . ($i + 2), $ApprovedBy);

                $approvedByTimeline = $aopApplication->applicationTimelines()
                    ->where('user_id', $mccChiefId)
                    ->orderByDesc('created_at')
                    ->first();

                $formattedDate = $approvedByTimeline
                    ? \Carbon\Carbon::parse($approvedByTimeline->created_at)->format('F j, Y')
                    : '____________';

                $richText = new RichText();
                $richText->createText('DATE: ');
                $richText->createTextRun($formattedDate)->getFont()->setUnderline(true);

                $sheet->setCellValue("M" . ($i + 5), $richText);
            }
        }

        $tempDirectory = storage_path('app/temp');
        if (!File::exists($tempDirectory)) {
            File::makeDirectory($tempDirectory, 0777, true); // Create directory if it doesn't exist
        }

        // Output the modified file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'aop_application_' . $id . '.xlsx';
        $tempPath = storage_path("app/temp/{$fileName}");
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    // Helper method to get the formatted objective description
    public function getObjectiveDescription($objective)
    {
        $description = $objective->objective->description ?? null;
        if ($description === 'Others' && $objective->othersObjective) {
            return $description . ': ' . $objective->othersObjective->description;
        }
        return $description;
    }

    // Helper method to get the formatted success indicator description
    public function getSuccessIndicatorDescription($objective)
    {
        $description = $objective->successIndicator->description ?? null;
        if ($description === 'Others' && $objective->otherSuccessIndicator) {
            return $description . ': ' . $objective->otherSuccessIndicator->description;
        }
        return $description;
    }

    private function getResponsiblePersonName($responsiblePerson)
    {

        if ($responsiblePerson->user_id) {
            return $responsiblePerson->user->name;
        }

        if ($responsiblePerson->division_id) {
            return $responsiblePerson->division->name;
        }
        if ($responsiblePerson->department_id) {
            return $responsiblePerson->department->name;
        }
        if ($responsiblePerson->section_id) {
            return $responsiblePerson->section->name;
        }
        if ($responsiblePerson->unit_id) {
            return $responsiblePerson->unit->name;
        }

        if ($responsiblePerson->designation_id) {
            return $responsiblePerson->designation->name;
        }

        return 'Unknown';
    }

    /**
     * This function is used to get the remarks per AOP application or request
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * Last edited by: Micah Mustaham, Updated by: Cascade
     */
    public function aopRemarks($id): JsonResponse
    {
        if (!$id) {
            return response()->json([
                'message' => 'AOP Application ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get the specific AOP application with user and divisionChief relationships
        $aopApplication = AopApplication::with([
            'user.assignedArea.designation',
            'divisionChief.assignedArea.designation'
        ])->find($id);

        if (!$aopApplication) {
            return response()->json([
                'message' => 'AOP Application not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Check if there are remarks for this application
        if (!$aopApplication->remarks) {
            return response()->json([
                'message' => 'No remarks found for this AOP application'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "message" => "Remarks retrieved successfully",
            "data" => new AopRemarksResource($aopApplication),
            "metadata" => $this->getMetadata('get')
        ], Response::HTTP_OK);
    }

    // SSR function not for API
    public function edit($id)
    {

        $aop_application = AopApplication::with([
            'applicationObjectives.activities.target',
            'applicationObjectives.activities.resources',
            'applicationObjectives.activities.resources.item',
            'applicationObjectives.activities.resources.purchaseType',
            'applicationObjectives.activities.responsiblePeople',
            'applicationObjectives.activities.responsiblePeople.user',
            'applicationObjectives.activities.responsiblePeople.division',
            'applicationObjectives.activities.responsiblePeople.department',
            'applicationObjectives.activities.responsiblePeople.section',
            'applicationObjectives.activities.responsiblePeople.unit',
            'applicationObjectives.activities.responsiblePeople.designation',
            'applicationObjectives.activities.comments',
            'applicationObjectives.objective.successIndicators',
            'applicationObjectives.objective.typeOfFunction',
            'applicationObjectives.objective.typeOfFunction.objectives',
            'applicationObjectives.objective.typeOfFunction.objectives.successIndicators',
            'applicationObjectives.successIndicator',
            'applicationObjectives.otherObjective',
            'applicationObjectives.otherSuccessIndicator',
        ])->findOrFail($id);

        if (!$aop_application) {
            return response()->json([
                'message' => 'AOP Application not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new AopResource($aop_application),
            'message' => 'AOP Application retrieved successfully'
        ], Response::HTTP_OK);
    }

    //In updating it should pass the exisitng application_objectives,activties,resources,and responsible person id
    public function storeAop(AopApplicationRequest $request)
    {
        if ($request->status === 'draft') {
            return $this->storeDraft($request);
        } else {
            return $this->storePending($request);
        }
    }


    private function storeDraft(AopApplicationRequest $request)
    {
        DB::beginTransaction();

        try {


            $validatedData = $request->validated();
            \Log::debug('Validated data:', $validatedData);
            $user_id = $request->user()->id;

            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();

            $existingAop = AopApplication::where('sector', $area['sector'])
                ->where('sector_id', $area['details']['id'])
                ->first();

            $divisionChiefId = $this->getDivisionChiefIdFromArea($area);

            $mccChiefId = optional(Division::where('name', 'Office of Medical Center Chief')->first())->head_id;
            if (!$mccChiefId) {
                return response()->json(['message' => 'Medical Center Chief not found.'], 404);
            }

            $planningOfficerId = optional(Section::where('name', 'Planning Unit')->first())->head_id;
            if (!$planningOfficerId) {
                return response()->json(['message' => 'Planning Officer not found.'], 404);
            }

            $budgetOfficerId = optional(Section::where('name', 'FS: Budget Section')->first())->head_id;
            if (!$budgetOfficerId) {
                return response()->json(['message' => 'Budget Officer not found.'], 404);
            }

            if ($existingAop) {
                // ✅ Update existing AOP
                $existingAop->update([
                    'user_id' => $user_id,
                    'division_chief_id' => $divisionChiefId,
                    'mcc_chief_id' => $mccChiefId,
                    'planning_officer_id' => $planningOfficerId,
                    'mission' => $validatedData['mission'] ?? $existingAop->mission,
                    'status' => $validatedData['status'] ?? $existingAop->status,
                    'has_discussed' => $validatedData['has_discussed'] ?? $existingAop->has_discussed,
                    'remarks' => $validatedData['remarks'] ?? $existingAop->remarks,
                ]);
                \Log::debug('Validated Data:', $validatedData);
                // ✅ Optionally add new objectives & activities
                if (!empty($validatedData['application_objectives'])) {
                    $this->syncObjectivesAndActivities($validatedData['application_objectives'], $existingAop);
                }

                Log::create([
                    'aop_application_id' => $existingAop->id,
                    'action' => "Update Aop Draft",
                    'action_by' => $user_id,
                ]);

                DB::commit();
                return response()->json(['message' => 'AOP draft updated successfully'], Response::HTTP_OK);
            }

            // ✅ Create new AOP application
            $aopApplication = AopApplication::create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'mcc_chief_id' => $mccChiefId,
                'planning_officer_id' => $planningOfficerId,
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission'] ?? null,
                'status' => $validatedData['status'],
                'sector' => $area['sector'],
                'sector_id' => $area['details']['id'],
                'has_discussed' => $validatedData['has_discussed'] ?? null,
                'remarks' => $validatedData['remarks'] ?? null,
                'year' => now()->year + 1,
            ]);

            if (!empty($validatedData['application_objectives'])) {
                $this->syncObjectivesAndActivities($validatedData['application_objectives'] ?? [], $aopApplication);
            }

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'action' => "Create Aop Draft",
                'action_by' => $user_id,
            ]);

            DB::commit();
            return response()->json(['message' => 'AOP draft saved successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function storePending(AopApplicationRequest $request)
    {
        $validatedData = $request->validated();
        $curr_user = User::find(2);
        // $curr_user = $request->user();
        if ($curr_user->authorization_pin !== $request->authorization_pin) {
            return response()->json(['message' => 'Invalid Authorization Pin'], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            \Log::debug('Validated data:', $validatedData);
            $user_id = 2;
            // $user_id = $request->user()->id;

            $assignedArea = AssignedArea::where('user_id', $user_id)->first();
            $area = $assignedArea->findDetails();

            $existingAop = AopApplication::where('sector', $area['sector'])
                ->where('sector_id', $area['details']['id'])
                ->first();

            $divisionChiefId = $this->getDivisionChiefIdFromArea($area);

            $mccChiefId = optional(Division::where('name', 'Office of Medical Center Chief')->first())->head_id;
            if (!$mccChiefId) {
                return response()->json(['message' => 'Medical Center Chief not found.'], 404);
            }

            $planningOfficerId = optional(Section::where('name', 'Planning Unit')->first())->head_id;
            if (!$planningOfficerId) {
                return response()->json(['message' => 'Planning Officer not found.'], 404);
            }

            $budgetOfficerId = optional(Section::where('name', 'FS: Budget Section')->first())->head_id;
            if (!$budgetOfficerId) {
                return response()->json(['message' => 'Budget Officer not found.'], 404);
            }

            if ($existingAop) {
                // ✅ Update existing AOP
                $existingAop->update([
                    'user_id' => $user_id,
                    'division_chief_id' => $divisionChiefId,
                    'mcc_chief_id' => $mccChiefId,
                    'planning_officer_id' => $planningOfficerId,
                    'mission' => $validatedData['mission'] ?? $existingAop->mission,
                    'status' => $validatedData['status'] ?? $existingAop->status,
                    'has_discussed' => $validatedData['has_discussed'] ?? $existingAop->has_discussed,
                    'remarks' => $validatedData['remarks'] ?? $existingAop->remarks,
                ]);



                // ✅ Optionally add new objectives & activities
                if (!empty($validatedData['application_objectives'])) {
                    $this->syncObjectivesAndActivities($validatedData['application_objectives'] ?? [], $existingAop);
                }

                // ✅ Handle PPMP Application creation or update
                $ppmpApplication = $existingAop->ppmpApplication;

                if (!$ppmpApplication) {
                    // If PPMP does not exist, create one
                    $ppmpApplication = $existingAop->ppmpApplication()->create([
                        'user_id' => $user_id,
                        'division_chief_id' => $divisionChiefId,
                        'budget_officer_id' => $budgetOfficerId,
                        'planning_officer_id' => $planningOfficerId,
                        'ppmp_application_uuid' => Str::uuid(),
                        'year' => now()->addYear()->year,
                        'status' => 'draft',
                    ]);
                }

                if ($ppmpApplication->wasRecentlyCreated) {
                    $this->createPpmpItemsFromActivities($existingAop, $ppmpApplication);
                } else {
                    $ppmpApplication->update([
                        'user_id' => $user_id,
                        'division_chief_id' => $divisionChiefId,
                        'budget_officer_id' => $budgetOfficerId,
                        'planning_officer_id' => $planningOfficerId,
                        'year' => now()->addYear()->year,
                        'status' => 'pending',
                    ]);

                    $this->syncPpmpItemsFromResources($existingAop, $ppmpApplication);
                    $this->updatePpmpTotal($existingAop, $ppmpApplication);
                }

                Log::create([
                    'aop_application_id' => $existingAop->id,
                    'action' => "Update Aop",
                    'action_by' => $user_id,
                ]);

                DB::commit();
                return response()->json(['message' => 'AOP Application updated successfully'], Response::HTTP_OK);
            }


            // ✅ Create new AOP application
            $aopApplication = AopApplication::create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'mcc_chief_id' => $mccChiefId,
                'planning_officer_id' => $planningOfficerId,
                'aop_application_uuid' => Str::uuid(),
                'mission' => $validatedData['mission'] ?? null,
                'status' => $validatedData['status'],
                'sector' => $area['sector'],
                'sector_id' => $area['details']['id'],
                'has_discussed' => $validatedData['has_discussed'] ?? null,
                'remarks' => $validatedData['remarks'] ?? null,
                'year' => now()->year + 1,
            ]);

            $aopApplication->ppmpApplication()->create([
                'user_id' => $user_id,
                'division_chief_id' => $divisionChiefId,
                'budget_officer_id' => $budgetOfficerId,
                'planning_officer_id' => $planningOfficerId,
                'ppmp_application_uuid' => Str::uuid(),
                'year' => now()->addYear()->year,
                'status' => 'pending',
            ]);

            if (!empty($validatedData['application_objectives'])) {
                $this->syncObjectivesAndActivities($validatedData['application_objectives'] ?? [], $aopApplication);
            }
            $ppmpApplication = $aopApplication->ppmpApplication;

            $this->createPpmpItemsFromActivities($aopApplication, $ppmpApplication);

            Log::create([
                'aop_application_id' => $aopApplication->id,
                'action' => "Create Aop",
                'action_by' => $user_id,
            ]);


            $approvalService = new ApprovalService($this->notificationService);
            $timelineResult = $approvalService->createInitialApplicationTimeline(
                $aopApplication,
                $curr_user,
                $request->remarks
            );

            if (!$timelineResult || (is_array($timelineResult) && !($timelineResult['success'] ?? true))) {
                DB::rollBack();
                return response()->json(['message' => 'Failed to create AOP application timeline'], 500);
            }

            DB::commit();
            return response()->json(['message' => 'AOP Application submitted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createPpmpItemsFromActivities(AopApplication $aopApplication, PpmpApplication $ppmpApplication)
    {
        foreach ($aopApplication->applicationObjectives as $objective) {
            foreach ($objective->activities as $activity) {
                foreach ($activity->resources as $resource) {
                    $item = $resource->item;

                    $ppmp_item = PpmpItem::create([
                        'ppmp_application_id' => $ppmpApplication->id,
                        'item_id' => $item->id,
                        'estimated_budget' => $item->estimated_budget,
                        'expense_class' => $resource->expense_class,
                    ]);

                    // Attach to activity
                    $activity->ppmpItems()->attach($ppmp_item->id);

                    // Create monthly schedule
                    for ($month = 1; $month <= 12; $month++) {
                        PpmpSchedule::create([
                            'ppmp_item_id' => $ppmp_item->id,
                            'month' => $month,
                            'year' => now()->addYear()->year,
                            'quantity' => 0,
                        ]);
                    }
                }
            }
        }
    }

    private function syncPpmpItemsFromResources(AopApplication $aopApplication, PpmpApplication $ppmpApplication)
    {
        // 🔥 Delete all existing PPMP items and related schedules & activity links
        foreach ($ppmpApplication->ppmpItems as $ppmpItem) {
            $ppmpItem->ppmpSchedule()->delete();       // delete schedules
            $ppmpItem->activities()->detach();      // detach activity links
            $ppmpItem->delete();                    // delete the item
        }

        // 🧾 Get all resources from this AOP
        $resources = Resource::whereHas('activity.applicationObjective', function ($q) use ($aopApplication) {
            $q->where('aop_application_id', $aopApplication->id);
        })->with(['item', 'activity'])->get();

        // ♻ Recreate PPMP items based on resources
        foreach ($resources as $resource) {
            $ppmpItem = PpmpItem::create([
                'ppmp_application_id' => $ppmpApplication->id,
                'item_id' => $resource->item_id,
                'estimated_budget' => $resource->item->estimated_budget,
                'expense_class' => $resource->expense_class,
            ]);

            // 🧩 Link to activity
            $resource->activity->ppmpItems()->attach($ppmpItem->id);

            // 📆 Create 12-month default schedules
            for ($month = 1; $month <= 12; $month++) {
                PpmpSchedule::create([
                    'ppmp_item_id' => $ppmpItem->id,
                    'month' => $month,
                    'year' => now()->addYear()->year,
                    'quantity' => 0
                ]);
            }
        }
    }
    private function syncObjectivesAndActivities(array $newObjectives, AopApplication $aopApplication)
    {
        // Get existing application objective IDs for this AOP application
        $existingObjectiveIds = $aopApplication->applicationObjectives()
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        // IDs sent from frontend
        $submittedObjectiveIds = collect($newObjectives)
            ->pluck('id')
            ->filter(fn($id) => !is_null($id))
            ->map(fn($id) => (int) $id)
            ->toArray();

        // Delete removed objectives
        $objectivesToDelete = array_diff($existingObjectiveIds, $submittedObjectiveIds);
        if (!empty($objectivesToDelete)) {
            ApplicationObjective::whereIn('id', $objectivesToDelete)->delete();
        }

        foreach ($newObjectives as $objectiveData) {
            // Determine if we're updating or creating
            $objective = isset($objectiveData['id'])
                ? ApplicationObjective::find($objectiveData['id'])
                : new ApplicationObjective();

            if (!$objective) {
                // In case the passed ID doesn't match any record (e.g. deleted on backend)
                $objective = new ApplicationObjective();
            }

            $objective->aop_application_id = $aopApplication->id;
            $objective->objective_id = $objectiveData['objective_id'];
            $objective->success_indicator_id = $objectiveData['success_indicator_id'] ?? null;
            $objective->save();

            // Handle 'Others' objective/indicator
            if ($objective->objective && $objective->objective->description === 'Others, please insert note/remarks' && isset($objectiveData['others_objective'])) {
                $objective->otherObjective()->updateOrCreate([], [
                    'description' => $objectiveData['others_objective'],
                ]);
            }

            if (
                $objective->successIndicator &&
                $objective->successIndicator->description === 'Others, please insert note/remarks' &&
                isset($objectiveData['other_success_indicator'])
            ) {
                $objective->otherSuccessIndicator()->updateOrCreate([], [
                    'description' => $objectiveData['other_success_indicator'],
                ]);
            }

            // Sync Activities
            $existingActivityIds = $objective->activities()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            // IDs sent from frontend
            $submittedActivityIds = collect($objectiveData['activities'])
                ->pluck('id')
                ->filter(fn($id) => !is_null($id))
                ->map(fn($id) => (int) $id)
                ->toArray();

            // Determine which activities to delete
            $activitiesToDelete = array_diff($existingActivityIds, $submittedActivityIds);

            if (!empty($activitiesToDelete)) {
                Activity::whereIn('id', $activitiesToDelete)->delete();
            }

            foreach ($objectiveData['activities'] ?? [] as $activityData) {
                if (empty($activityData['name'])) {
                    \Log::warning('Skipping activity due to missing name.', ['activityData' => $activityData]);
                    continue;
                }

                $activity = isset($activityData['id'])
                    ? Activity::find($activityData['id'])
                    : null;

                if (!$activity) {
                    $activity = $objective->activities()->create([
                        'activity_code' => $this->generateUniqueActivityCode(),
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);
                } else {
                    $activity->update([
                        'name' => $activityData['name'],
                        'is_gad_related' => $activityData['is_gad_related'],
                        'cost' => $activityData['cost'],
                        'start_month' => $activityData['start_month'],
                        'end_month' => $activityData['end_month'],
                    ]);
                }

                // Sync target (1:1)
                if (isset($activityData['target'])) {
                    if ($activity->target) {
                        $activity->target->update($activityData['target']);
                    } else {
                        $activity->target()->create($activityData['target']);
                    }
                }

                // Sync resources (1:N)
                if (isset($activityData['resources'])) {
                    $existingResourceIds = $activity->resources()
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->toArray();

                    $submittedResourceIds = collect($activityData['resources'] ?? [])
                        ->pluck('id')
                        ->filter(fn($id) => !is_null($id))
                        ->map(fn($id) => (int) $id)
                        ->toArray();

                    $resourcesToDelete = array_diff($existingResourceIds, $submittedResourceIds);
                    if (!empty($resourcesToDelete)) {
                        Resource::whereIn('id', $resourcesToDelete)->delete();
                    }

                    foreach ($activityData['resources'] as $resourceData) {
                        $resource = isset($resourceData['id']) ? Resource::find($resourceData['id']) : null;

                        if ($resource) {
                            $resource->update($resourceData);
                        } else {
                            $item = Item::find($resourceData['item_id']);
                            $activity->resources()->create(array_merge($resourceData, [
                                'item_cost' => $item?->estimated_budget ?? 0
                            ]));
                        }
                    }
                }

                // Sync responsible people (1:N)
                if (isset($activityData['responsible_people'])) {
                    // Sync Responsible People
                    $existingPersonIds = $activity->responsiblePeople()
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->toArray();

                    $submittedPersonIds = collect($activityData['responsible_people'] ?? [])
                        ->pluck('id')
                        ->filter(fn($id) => !is_null($id))
                        ->map(fn($id) => (int) $id)
                        ->toArray();

                    $peopleToDelete = array_diff($existingPersonIds, $submittedPersonIds);
                    if (!empty($peopleToDelete)) {
                        ResponsiblePerson::whereIn('id', $peopleToDelete)->delete();
                    }

                    foreach ($activityData['responsible_people'] as $personData) {
                        \Log::info('Processing Responsible Person', ['personData' => $personData]);
                        $person = isset($personData['id']) ? ResponsiblePerson::find($personData['id']) : null;
                        if ($person) {
                            $person->update($personData);
                        } else {
                            $activity->responsiblePeople()->create($personData);
                        }
                    }
                }
            }
        }
    }


    private function deleteActivityWithRelations(Activity $activity)
    {
        $aopApplication = $activity->objective->aopApplication;
        $ppmpApplication = $aopApplication?->ppmpApplication;

        if ($ppmpApplication) {
            foreach ($activity->resources as $resource) {
                \App\Models\PpmpItem::where('ppmp_application_id', $ppmpApplication->id)
                    ->where('item_id', $resource->item_id)
                    ->delete();
            }
        }

        // Now delete related data
        $activity->resources()->delete();
        $activity->responsiblePeople()->delete();
        $activity->target()?->delete();
        $activity->delete();
    }

    private function deleteObjectiveWithRelations(ApplicationObjective $objective)
    {
        foreach ($objective->activities as $activity) {
            $this->deleteActivityWithRelations($activity);
        }

        $objective->otherObjective()?->delete();
        $objective->otherSuccessIndicator()?->delete();
        $objective->delete();
    }

    public function destroyActivities(Activity $aopActivity)
    {
        $this->deleteActivityWithRelations($aopActivity);
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function destroyObjectives(ApplicationObjective $aopObjective)
    {
        $this->deleteObjectiveWithRelations($aopObjective);
        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function destroyResource(Resource $aopResource)
    {

        $activity = $aopResource->activity;
        $objective = $activity->objective;
        $aopApplication = $objective->aopApplication;


        $ppmpApplication = $aopApplication->ppmpApplication;


        if ($ppmpApplication) {
            \App\Models\PpmpItem::where('ppmp_application_id', $ppmpApplication->id)
                ->where('item_id', $aopResource->item_id)
                ->delete();
        }


        $aopResource->delete();

        return response()->json([], \Illuminate\Http\Response::HTTP_NO_CONTENT);
    }

    public function destroyResponsiblePerson(ResponsiblePerson $responsiblePerson)
    {
        $responsiblePerson->delete();
        return response()->json([], Response::HTTP_NO_CONTENT);
    }


    private function getDivisionChiefIdFromArea(array $area): ?int
    {
        switch ($area['sector']) {
            case 'Division':
                $division = Division::where('name', $area['details']['name'])->first();
                return $division?->head_id;

            case 'Department':
                $department = Department::where('name', $area['details']['name'])->first();
                $division = Division::find($department?->division_id);
                return $division?->head_id;

            case 'Section':
                $section = Section::where('name', $area['details']['name'])->first();
                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {
                    $division = Division::find($section?->division_id);
                }
                return $division?->head_id;

            case 'Unit':
                $unit = Unit::where('name', $area['details']['name'])->first();
                $section = Section::find($unit?->section_id);
                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {
                    $division = Division::find($section?->division_id);
                }
                return $division?->head_id;

            default:
                return null;
        }
    }
    private function getExistingAop($area)
    {
        return AopApplication::where('sector', $area['sector'])
            ->where('sector_id', $area['details']['id'])
            ->first();
    }

    private function getDivisionChiefId($area)
    {
        switch ($area['sector']) {
            case 'Division':
                $division = Division::where('name', $area['details']['name'])->first();
                $divisionChiefId = $division->head_id;
                break;
            case 'Department':
                $department = Department::where('name', $area['details']['name'])->first();
                $division = Division::where('id', $department->division_id)->first();
                $divisionChiefId = $division->head_id;
                break;
            case 'Section':
                $section = Section::where('name', $area['details']['name'])->first();
                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {
                    $division = Division::find($section?->division_id);
                }

                $divisionChiefId = $division?->head_id ?? null;

                break;
            case 'Unit':
                $unit = Unit::where('name', $area['details']['name'])->first();
                $section = Section::where('id', $unit->section_id)->first();

                if ($section?->department_id) {
                    $department = Department::find($section->department_id);
                    $division = Division::find($department?->division_id);
                } else {

                    $division = Division::find($section?->division_id);
                }
                $divisionChiefId = $division?->head_id ?? null;
                break;
        }
    }

    private function updatePpmpTotal(AopApplication $aopApplication, PpmpApplication $ppmpApplication): void
    {
        $procurablePurchaseTypeId = PurchaseType::where('description', 'Procurable')->value('id');
        $ppmpTotal = 0;

        foreach ($aopApplication->applicationObjectives as $objective) {
            foreach ($objective->activities as $activity) {
                foreach ($activity->resources as $resource) {
                    if (
                        $resource->purchase_type_id === $procurablePurchaseTypeId &&
                        $resource->item
                    ) {
                        $ppmpTotal += $resource->quantity * $resource->item_cost;
                    }
                }
            }
        }

        $ppmpApplication->update(['ppmp_total' => $ppmpTotal]);
    }

    private function getApproverUserIds(): array
    {
        $medicalCenterChiefDivision = Division::where('name', 'Office of Medical Center Chief')->first();
        $mccChiefId = optional($medicalCenterChiefDivision)->head_id;

        if (is_null($mccChiefId)) {
            throw new \Exception('Medical Center Chief not found.');
        }

        $planningOfficer = Section::where('name', 'Planning Unit')->first();
        $planningOfficerId = optional($planningOfficer)->head_id;

        if (is_null($planningOfficerId)) {
            throw new \Exception('Planning Officer not found.');
        }

        $budgetOfficer = Section::where('name', 'FS: Budget Section')->first();
        $budgetOfficerId = optional($budgetOfficer)->head_id;

        if (is_null($budgetOfficerId)) {
            throw new \Exception('Budget Officer not found.');
        }

        return [
            'mcc_chief_id' => $mccChiefId,
            'planning_officer_id' => $planningOfficerId,
            'budget_officer_id' => $budgetOfficerId,
        ];
    }
}
