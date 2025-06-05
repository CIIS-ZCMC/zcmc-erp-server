<?php

namespace App\Http\Controllers;

use App\Exports\PpmpItemExport;
use App\Http\Requests\PpmpItemRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Http\Resources\PpmpItemResource;
use App\Models\Activity;
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

        $ppmp_item = PpmpItem::with([
            'ppmpApplication.user',
            'ppmpApplication.divisionChief',
            'ppmpApplication.budgetOfficer',
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
            $ppmp_item->whereHas('ppmpApplication', function ($query) use ($year, $sector) {
                $query->where('year', $year)
                    ->with([
                        'aopApplication' => function ($query) use ($sector) {
                            $query->where('sector_id', $sector['details']['id'])
                                ->where('sector', $sector['details']['name']);
                        }
                    ]);
            });
        }

        $result = $ppmp_item->whereNull('deleted_at')
            ->orderBy('id')
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

        return $result;
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

        if (!$ppmp_item) {
            return response()->json([
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

        if ($user_authorization_pin !== $request->pin) {
            return response()->json([
                'message' => 'Invalid Authorization Pin'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ppmp_application = PpmpApplication::with([
            'ppmpItems' => function ($query) {
                $query->with([
                    'item',
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
            },
            'aopApplication' => function ($query) use ($sector) {
                $query->where('sector_id', $sector['details']['id'])
                    ->where('sector', $sector['details']['name']);
            }
        ])->where('year', $year)->first();

        if (!$ppmp_application) {
            return response()->json([
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        } elseif ($ppmp_application->status === 'submitted') {
            return response()->json(
                ['message' => "Looks like you've already submitted this application. You can't submit it again."],
                Response::HTTP_FOUND
            );
        } else {
            $draft = false;

            if ($request->is_draft === 1) {
                $draft = true;
                $ppmp_application->update(['status' => 'draft', 'is_draft' => $draft]);
            } else {
                $ppmp_application->update(['status' => 'pending']);
            }
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

        $ppmpItems = json_decode($request['PPMP_Items'], true);

        foreach ($ppmpItems as $item) {
            $procurement_mode = null;
            if ($item['procurement_mode'] !== null) {
                $procurement_mode = ProcurementModes::where('name', $item['procurement_mode']['name'])->first()->id;

                if (!$procurement_mode) {
                    return response()->json([
                        'message' => 'Procurement mode not found.',
                    ], Response::HTTP_NOT_FOUND);
                }
            }

            if ($request->is_draft === "0" && $item['procurement_mode'] !== null) {
                return response()->json([
                    'message' => 'Procurement mode is required.',
                ], Response::HTTP_NOT_ACCEPTABLE);
            }

            $items = Item::where('code', $item['item_code'])->first();
            if (!$items) {
                return response()->json([
                    'message' => 'Item not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            $find_ppmp_item = PpmpItem::where('item_id', $items->id)->first();
            $ppmp_data = [
                'ppmp_application_id' => $ppmp_application->id,
                'item_id' => $items->id,
                'procurement_mode_id' => $procurement_mode,
                'item_request_id' => $item['item_request_id'] ?? null,
                'remarks' => $item['remarks'] ?? null,
                'estimated_budget' => $item['estimated_budget'] ?? 0,
                'total_quantity' => $item['quantity'] ?? 0,
                'total_amount' => $item['total_amount'] ?? 0,
            ];

            if (!$find_ppmp_item) {
                $ppmpItem = PpmpItem::create($ppmp_data); // Create PPMP item
            } else {
                $ppmpItem = $find_ppmp_item;
                $ppmpItem->update($ppmp_data); // Update PPMP item
            }

            foreach ($item['activities'] as $activity) {
                $activities = Activity::find($activity['activity_id'] ?? $activity['id'] ?? null);

                if ($activities) {
                    $activity_ppmp_item = $activities->ppmpItems()
                        ->where('activity_id', $activities->id)
                        ->where('ppmp_item_id', $ppmpItem->id)
                        ->first();

                    if (!$activity_ppmp_item) {
                        $activities->ppmpItems()->attach($ppmpItem->id, [
                            'remarks' => $ppmpItem['remarks'] ?? null,
                        ]);

                        $resource_request = [
                            'activity_id' => $activities->id,
                            'item_id' => $items->id,
                            'purchase_type_id' => $item['purchase_type_id'] ?? 1,
                            'quantity' => $item['aop_quantity'] ?? 0,
                            'expense_class' => $item['expense_class'],
                        ];

                        $resource = Resource::where('activity_id', $activities->id)
                            ->where('item_id', $items->id)
                            ->first();

                        if ($resource) {
                            $resource->update($resource_request);
                        } else {
                            Resource::create($resource_request);
                        }

                    } else {
                        $activity_ppmp_item->pivot->remarks = $item['remarks'];
                        $activity_ppmp_item->pivot->save();

                        $resource_request = [
                            'activity_id' => $activities->id,
                            'item_id' => $items->id,
                            'purchase_type_id' => $item['purchase_type_id'] ?? 1,
                            'quantity' => $item['aop_quantity'] ?? 0,
                            'expense_class' => $item['expense_class'],
                        ];

                        $resource = Resource::where('activity_id', $activities->id)
                            ->where('item_id', $items->id)
                            ->first();

                        if ($resource) {
                            $resource->update($resource_request);
                        } else {
                            Resource::create($resource_request);
                        }
                    }
                }
            }

            $total_quantity = 0;
            foreach ($item['target_by_quarter'] as $monthly => $quantity) {
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

        if ($request->is_draft === true) {
            $ppmp_application->update(['status' => 'draft', 'is_draft' => true]);
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

        DB::commit();

        $ppmp_application->load([
            'ppmpItems' => function ($query) {
                $query->with([
                    'item',
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
            },
            'aopApplication'
        ]);

        return response()->json([
            'data' => new PpmpApplicationResource($ppmp_application),
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
            'ppmp_' . $area['details']['code'] . '_' . $year . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
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
