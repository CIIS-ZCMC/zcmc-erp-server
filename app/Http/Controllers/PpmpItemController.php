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

        $year = $request->query('year', now()->year + 1);
        $user = User::find($request->user()->id);
        $sector = $user->assignedArea->findDetails();
        $user_authorization_pin = $user->authorization_pin;

        if ($request->is_draft === "0") {
            if ($user_authorization_pin !== $request->pin) {
                return response()->json([
                    'message' => 'Invalid Authorization Pin'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $aop_application = AopApplication::where('year', $year)
            ->where('sector_id', $sector['details']['id'])
            ->where('sector', $sector['sector'])
            ->first();

        if (!$aop_application) {
            return response()->json([
                'message' => "No AOP application found for the specified year and sector.",
            ], Response::HTTP_NOT_FOUND);
        }

        $ppmp_application = PpmpApplication::where('aop_application_id', $aop_application->id)
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if (!$ppmp_application) {
            return response()->json([
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        } elseif ($ppmp_application->status === 'pending') {
            return response()->json(
                ['message' => "Looks like you've already submitted this application. You can't submit it again."],
                Response::HTTP_FOUND
            );
        }

        if ($request->is_draft === "0") {
            $ppmp_application->update(['status' => 'pending', 'is_draft' => false]);
        } elseif ($request->is_draft === "1") {
            $ppmp_application->update(['status' => 'draft', 'is_draft' => true]);
        }

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

        $ppmp_items = json_decode($request['PPMP_Items'], true);

        if ($ppmp_items !== null) {
            foreach ($ppmp_items as $i) {
                $procurement_mode = null;
                if ($i['procurement_mode'] !== null) {
                    $procurement = isset($i['procurement_mode']['name']) ? $i['procurement_mode']['name'] : $i['procurement_mode'];
                    $procurement_mode = ProcurementModes::where('name', $procurement)->first()->id;

                    if (!$procurement_mode) {
                        return response()->json([
                            'message' => 'Procurement mode not found.',
                        ], Response::HTTP_NOT_FOUND);
                    }
                }

                if ($request->is_draft === "0" && $i['procurement_mode'] === null) {
                    return response()->json([
                        'message' => 'Procurement mode is required.',
                    ], Response::HTTP_NOT_ACCEPTABLE);
                }

                $items = Item::where('code', $i['item_code'])->first();
                if (!$items) {
                    return response()->json([
                        'message' => 'Item not found.',
                    ], Response::HTTP_NOT_FOUND);
                }

                $find_ppmp_item = PpmpItem::where('id', $i['id'])->first();

                $ppmp_data = [
                    'ppmp_application_id' => $ppmp_application->id,
                    'item_id' => $items->id,
                    'procurement_mode_id' => $procurement_mode,
                    'item_request_id' => $i['item_request_id'] ?? null,
                    'remarks' => $i['remarks'] ?? null,
                    'estimated_budget' => $i['estimated_budget'] ?? 0,
                    'total_quantity' => $i['quantity'] ?? 0,
                    'total_amount' => $i['total_amount'] ?? 0,
                ];

                if (!$find_ppmp_item) {
                    $ppmpItem = PpmpItem::create($ppmp_data); // Create PPMP item
                } else {
                    $ppmpItem = $find_ppmp_item;
                    $ppmpItem->update($ppmp_data); // Update PPMP item
                }

                foreach ($i['activities'] as $activity) {
                    $activity_id = isset($activity['activity_id']) ? $activity['activity_id'] : $activity['id'];
                    $activities = Activity::find($activity_id);

                    if ($activities) {
                        $exists = $activities->ppmpItems()
                            ->where('ppmp_item_id', $ppmpItem->id)
                            ->wherePivot('activity_id', $activities->id) // optional, usually redundant in this direction
                            ->exists();

                        if (!$exists) {
                            $activities->ppmpItems()->attach($ppmpItem->id, [
                                'remarks' => $ppmpItem['remarks'],
                            ]);
                        } else {
                            $activities->ppmpItems()->updateExistingPivot($ppmpItem->id, [
                                'remarks' => $ppmpItem['remarks'],
                            ]);
                        }

                        $resource = Resource::where('activity_id', $activities->id)
                            ->where('item_id', $items->id)
                            ->first();

                        $resource_request = [
                            'activity_id' => $activities->id,
                            'item_id' => $items->id,
                            'purchase_type_id' => $resource->purchase_type_id ?? 1,
                            'quantity' => $resource->quantity ?? $activity['activity_quantity'],
                            'expense_class' => $ppmpItem['expense_class'],
                        ];

                        if ($resource) {
                            $resource->update($resource_request);
                        } else {
                            Resource::create($resource_request);
                        }
                    }
                }

                $total_quantity = 0;
                foreach ($i['target_by_quarter'] as $monthly => $quantity) {
                    if ($quantity >= 0 && isset($monthMap[$monthly])) {
                        $total_quantity += $quantity;

                        $target_request = [
                            'ppmp_item_id' => $ppmpItem->id,
                            'month' => $monthMap[$monthly],
                            'year' => now()->addYear()->year,
                            'quantity' => $quantity,
                        ];

                        $ppmp_schedule = PpmpSchedule::where('ppmp_item_id', $ppmpItem->id)
                            ->where('month', $monthMap[$monthly])
                            ->where('year', now()->addYear()->year)
                            ->first();

                        if ($ppmp_schedule) {
                            $ppmp_schedule->update($target_request);
                        } else {
                            PpmpSchedule::create($target_request);
                        }
                    }
                }

                $ppmpItem->update(['total_quantity' => $total_quantity]);
            }

            $planning_officer = User::find($ppmp_application->planning_officer_id);
            $status = $ppmp_application->status;
            $description = [
                'title' => 'PPMP Application Requires Your Action',
                'description' => "An Pmpp application has been routed to you for review.",
                'module_path' => "/ppmp-application",
                'pmpp_application_id' => $ppmp_application->id,
                'status' => $status
            ];


            $this->notificationService->notify($planning_officer, $description);
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

        $data = [
            'ppmp_application' => $ppmp_application,
            'ppmp_items' => $ppmp_item
        ];

        return response()->json([
            'data' => new PpmpItemResource($data),
            'message' => 'PPMP Items created successfully.',
        ], Response::HTTP_CREATED);
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
