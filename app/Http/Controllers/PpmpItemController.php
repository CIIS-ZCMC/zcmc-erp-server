<?php

namespace App\Http\Controllers;

use App\Exports\PpmpItemExport;
use App\Http\Requests\PpmpItemRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Http\Resources\PpmpItemResource;
use App\Models\Activity;
use App\Models\AopApplication;
use App\Models\Item;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\ProcurementModes;
use App\Models\Resource;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemController extends Controller
{
    private $is_development;

    private $module = 'ppmp_items';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->is_development = env("APP_DEBUG", true);
    }

    private function getPpmpItems(Request $request)
    {
        $year = $request->query('year', now()->year + 1);
        $user = User::find($request->user()->id);
        $sector = $user->assignedArea->findDetails();
        $ppmp_application_id = $request->ppmp_application_id;

        $aop_application = AopApplication::where('year', $year)
            ->where('sector_id', $sector['details']['id'])
            ->where('sector', $sector['sector'])
            ->first();

        if ($aop_application) {
            $ppmp_application = PpmpApplication::where('aop_application_id', $aop_application->id)->first();

            if ($ppmp_application) {
                $ppmp_item = PpmpItem::with([
                    'ppmpApplication.user',
                    'ppmpApplication.divisionChief',
                    'ppmpApplication.budgetOfficer',
                    'ppmpApplication.planningOfficer',
                    'ppmpApplication.aopApplication',
                    'item.itemUnit',
                    'item.itemCategory',
                    'item.itemClassification',
                    'item.itemSpecifications',
                    'item.terminologyCategory',
                    'procurementMode',
                    'itemRequest',
                    'activities',
                    'comments',
                    'ppmpSchedule',
                ]);

                if ($ppmp_application_id !== null) {
                    $ppmp_item->where('ppmp_application_id', $ppmp_application_id);
                } else {
                    $ppmp_item->whereHas('ppmpApplication', function ($query) use ($ppmp_application, $aop_application) {
                        $query->where('id', $ppmp_application->id)->where('aop_application_id', $aop_application->id);
                    });
                }

                $ppmp_items = $ppmp_item->whereNull('deleted_at')->get();

                return [
                    'ppmp_application' => $ppmp_application,
                    'ppmp_items' => $ppmp_items
                ];
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($request->search !== null) {
            return $this->search($request);
        }

        if ($request->export) {
            return $this->export($request);
        }

        $ppmp_item = $this->getPpmpItems($request);

        if (!$ppmp_item || empty($ppmp_item['ppmp_items'])) {
            return response()->json([
                'data' => (object) [],
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new PpmpItemResource($ppmp_item),
            'message' => 'PPMP Items retrieved successfully.',
        ], Response::HTTP_OK);
    }

    public function store(PpmpItemRequest $request)
    {
        DB::beginTransaction();
        try {
            $year = $request->query('year', now()->year + 1);
            $user = User::find($request->user()->id);
            $sector = $user->assignedArea->findDetails();
            $user_authorization_pin = $user->authorization_pin;

            if ($request->is_draft === "0" && $user_authorization_pin !== $request->pin) {
                return response()->json(['message' => 'Invalid Authorization Pin'], 400);
            }

            $aop_application = AopApplication::where('year', $year)
                ->where('sector_id', $sector['details']['id'])
                ->where('sector', $sector['sector'])
                ->firstOrFail();

            $ppmp_application = PpmpApplication::where('aop_application_id', $aop_application->id)
                ->where('user_id', $user->id)
                ->where('year', $year)
                ->firstOrFail();

            if ($ppmp_application->status === 'pending') {
                return response()->json(['message' => "You've already submitted this application."], 302);
            }

            $ppmp_application->update([
                'status' => $request->is_draft === "0" ? 'pending' : 'draft',
                'is_draft' => $request->is_draft === "1"
            ]);

            $ppmp_items = json_decode($request['PPMP_Items'], true);

            $monthMap = [
                'jan' => 1,
                'feb' => 2,
                'mar' => 3,
                'apr' => 4,
                'may' => 5,
                'jun' => 6,
                'jul' => 7,
                'aug' => 8,
                'sep' => 9,
                'oct' => 10,
                'nov' => 11,
                'dec' => 12,
            ];

            $incomingPpmpItemIds = [];

            foreach ($ppmp_items as $i) {
                $items = Item::where('code', $i['item_code'])->firstOrFail();
                $procurementName = $i['procurement_mode']['name'] ?? null;

                $procurementModeId = null;
                if ($procurementName) {
                    $procurement = ProcurementModes::where('name', $procurementName)->first();
                    if (!$procurement) return response()->json(['message' => 'Procurement mode not found'], 404);
                    $procurementModeId = $procurement->id;
                } elseif ($request->is_draft === "0") {
                    return response()->json(['message' => 'Procurement mode is required.'], 406);
                }

                $estimatedBudget = $items->estimated_budget ?? 0;
                $totalQuantity = array_sum($i['target_by_quarter'] ?? []);
                $totalAmount = $estimatedBudget * $totalQuantity;

                $ppmp_data = [
                    'ppmp_application_id' => $ppmp_application->id,
                    'item_id' => $items->id,
                    'procurement_mode_id' => $procurementModeId,
                    'item_request_id' => $i['item_request_id'] ?? null,
                    'remarks' => $i['remarks'] ?? null,
                    'estimated_budget' => $estimatedBudget,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                ];

                $ppmpItem = PpmpItem::updateOrCreate(
                    ['id' => $i['id'] ?? 0],
                    $ppmp_data
                );

                $incomingPpmpItemIds[] = $ppmpItem->id;

                $scheduleMonths = [];

                foreach ($i['target_by_quarter'] ?? [] as $monthKey => $qty) {
                    $month = $monthMap[$monthKey] ?? null;
                    if (!$month || $qty < 0) continue;

                    $scheduleMonths[] = $month;

                    PpmpSchedule::updateOrCreate(
                        [
                            'ppmp_item_id' => $ppmpItem->id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        ['quantity' => $qty]
                    );
                }

                // Get soft-deleted ppmp_item IDs under this application
                $softDeletedItemIds = PpmpItem::onlyTrashed()
                    ->where('ppmp_application_id', $ppmp_application->id)
                    ->pluck('id')
                    ->toArray();

                // Delete related schedules
                if (!empty($softDeletedItemIds)) {
                    PpmpSchedule::whereIn('ppmp_item_id', $softDeletedItemIds)->delete();
                }

                $existingScheduleItemIds = PpmpItem::where('ppmp_application_id', $ppmp_application->id)
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->toArray();

                PpmpSchedule::where('ppmp_item_id', '!=', null)
                    ->whereNotIn('ppmp_item_id', $existingScheduleItemIds)
                    ->delete();

                foreach ($i['activities'] as $activity) {
                    $activity_id = $activity['activity_id'] ?? $activity['id'];
                    $activityModel = Activity::find($activity_id);
                    if (!$activityModel) continue;

                    $activityModel->ppmpItems()->syncWithoutDetaching([
                        $ppmpItem->id => ['remarks' => $ppmpItem->remarks],
                    ]);

                    Resource::updateOrCreate(
                        [
                            'activity_id' => $activityModel->id,
                            'item_id' => $items->id,
                        ],
                        [
                            'purchase_type_id' => 1,
                            'quantity' => $i['quantity'] ?? 1,
                            'expense_class' => $i['expense_class'] ?? 'MOOE',
                            'item_cost' => $estimatedBudget,
                        ]
                    );

                    // ✅ FIXED: Recompute using fresh query, not stale relationship
                    $totalActivityCost = $activityModel->resources()->get()->sum(function ($r) {
                        return $r->quantity * $r->item_cost;
                    });

                    $activityModel->update(['cost' => $totalActivityCost]);
                }
            }

            // ❗ Delete old PPMP items that were removed
            $existingIds = PpmpItem::where('ppmp_application_id', $ppmp_application->id)->pluck('id')->toArray();
            $toDelete = array_diff($existingIds, $incomingPpmpItemIds);
            if (!empty($toDelete)) {
                PpmpItem::whereIn('id', $toDelete)->delete();
            }

            // 🔔 Notify planning officer
            $planning_officer = User::find($ppmp_application->planning_officer_id);
            if ($planning_officer) {
                $this->notificationService->notify($planning_officer, [
                    'title' => 'PPMP Application Requires Your Action',
                    'description' => 'A PPMP application has been routed to you for review.',
                    'module_path' => '/ppmp-application',
                    'pmpp_application_id' => $ppmp_application->id,
                    'status' => $ppmp_application->status,
                ]);
            }

            DB::commit();

            $ppmp_item = PpmpItem::with([
                'ppmpApplication.user',
                'ppmpApplication.divisionChief',
                'ppmpApplication.budgetOfficer',
                'ppmpApplication.planningOfficer',
                'ppmpApplication.aopApplication',
                'item.itemUnit',
                'item.itemCategory',
                'item.itemClassification',
                'item.itemSpecifications',
                'item.terminologyCategory',
                'procurementMode',
                'itemRequest',
                'activities',
                'comments',
                'ppmpSchedule',
            ])->where('ppmp_application_id', $ppmp_application->id)
                ->whereNull('deleted_at')
                ->get();

            return response()->json([
                'data' => new PpmpItemResource([
                    'ppmp_application' => $ppmp_application,
                    'ppmp_items' => $ppmp_item,
                ]),
                'message' => 'PPMP Items saved successfully.',
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('PPMP Store Failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }



    public function destroy(Request $request)
    {
        // Get the current user and its area
        $curr_user = User::find($request->user()->id);
        $curr_user_authorization_pin = $curr_user->authorization_pin;

        if ($curr_user_authorization_pin !== $request->authorization_pin) {
            return response()->json([
                'message' => 'Invalid Authorization Pin'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => "PPMP Item deleted successfully."
        ], Response::HTTP_OK);
    }

    public function export(Request $request)
    {
        $data = $this->getPpmpItems($request);

        $user_id = $data['ppmp_application']->user_id;
        $user = User::find($user_id);

        $area = $user->assignedArea->findDetails();
        $year = $data['ppmp_application']->year;

        if (!$data) {
            abort(404, 'No record found.');
        }

        return Excel::download(
            new PpmpItemExport($data),
            'ppmp_' . $area['details']['code'] . '_' . $year . '.xlsx'
        );
    }

    public function search(Request $request)
    {
        $term = $request->input('search');
        $terms = $term ? explode(' ', $term) : [];

        $year = $request->query('year', now()->year + 1);
        $user = User::find($request->user()->id);
        $sector = $user->assignedArea->findDetails();

        $ppmp_item = PpmpItem::with([
            'ppmpApplication.user',
            'ppmpApplication.divisionChief',
            'ppmpApplication.budgetOfficer',
            'ppmpApplication.aopApplication',
            'item.itemUnit',
            'item.itemCategory',
            'item.itemClassification',
            'item.itemSpecifications',
            'procurementMode',
            'itemRequest',
            'activities',
            'comments',
            'ppmpSchedule',
        ])
            ->whereHas('ppmpApplication', function ($query) use ($year, $sector) {
                $query->where('year', $year)
                    ->with([
                        'aopApplication' => function ($query) use ($sector) {
                            $query->where('sector_id', $sector['details']['id'])
                                ->where('sector', $sector['details']['name']);
                        }
                    ]);
            })
            ->whereNull('deleted_at')
            ->search($terms)
            ->get()
            ->groupBy(function ($item) {
                $app = $item->ppmpApplication;
                return $app ? "{$app->year}_{$app->aopApplication->sector_id}" : 'undefined';
            })
            ->map(function ($items) {
                return [
                    'ppmp_application' => $items->first()->ppmpApplication,
                    'ppmp_items' => $items
                ];
            })->first();

        return response()->json([
            'data' => new PpmpItemResource($ppmp_item),
            'message' => "PPMP Items retrieved successfully."
        ], Response::HTTP_OK);
    }
}
