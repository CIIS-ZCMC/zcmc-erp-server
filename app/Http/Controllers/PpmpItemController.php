<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpItemRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Http\Resources\PpmpItemResource;
use App\Models\Activity;
use App\Models\ActivityPpmpItem;
use App\Models\Item;
use App\Models\PpmpApplication;
use App\Models\PpmpItem;
use App\Models\PpmpSchedule;
use App\Models\ProcurementModes;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemController extends Controller
{
    private $is_development;

    private $module = 'ppmp_items';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    public function index(Request $request)
    {
        $ppmp_application = PpmpApplication::with([
            'user',
            'divisionChief',
            'budgetOfficer',
            'aopApplication',
            'ppmpItems' => function ($query) {
                $query->with([
                    'item' => function ($query) {
                        $query->with([
                            'itemUnit',
                            'itemCategory',
                            'itemClassification',
                            'itemSpecifications',
                        ]);
                    },
                    'procurementMode',
                    'itemRequest',
                    'activities',
                    'comments',
                    'ppmpSchedule',
                ]);
            },
        ])->whereNull('deleted_at')->latest()->first();

        if (!$ppmp_application) {
            return response()->json([
                'message' => "No record found.",
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new PpmpApplicationResource($ppmp_application),
            'message' => 'PPMP Application retrieved successfully.',
        ], Response::HTTP_OK);
    }

    public function store(PpmpItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $createdItems = [];

            $ppmp_application = PpmpApplication::latest()->first();

            if (!$ppmp_application) {
                return response()->json([
                    'message' => "No record found.",
                ], Response::HTTP_NOT_FOUND);
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
                $procurement_mode = ProcurementModes::where('name', $item['procurement_mode']['name'])->first();
                if (!$procurement_mode) {
                    return response()->json([
                        'message' => 'Procurement mode not found.',
                    ], 404);
                }

                $items = Item::where('code', $item['item_code'])->first();
                if (!$items) {
                    return response()->json([
                        'message' => 'Item not found.',
                    ], 404);
                }

                $find_ppmp_item = PpmpItem::where('item_id', $items->id)->first();
                $ppmp_data = [
                    'ppmp_application_id' => 1,
                    'item_id' => $items->id,
                    'procurement_mode_id' => $procurement_mode->id,
                    'item_request_id' => $item['item_request_id'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                    'estimated_budget' => $item['estimated_budget'] ?? 0,
                    'total_quantity' => $item['quantity'] ?? 0,
                    'total_amount' => $item['total_amount'] ?? 0,
                ];

                if (!$find_ppmp_item) {
                    // Create PPMP item
                    $ppmpItem = PpmpItem::create($ppmp_data);
                } else {
                    // Update PPMP item
                    $ppmpItem = $find_ppmp_item;
                    $ppmpItem->update($ppmp_data);
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
                                'remarks' => $item['remarks'] ?? null,
                                'is_draft' => $request->is_draft ?? 0,
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
                        }
                    }
                }

                foreach ($item['target_by_quarter'] as $monthly => $quantity) {
                    if ($quantity >= 0 && isset($monthMap[$monthly])) {
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
            }

            DB::commit();

            return response()->json([
                'data' => new PpmpApplicationResource($ppmp_application),
                'message' => 'PPMP Items created successfully.',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error creating PPMP Items: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(PpmpItem $ppmpItem)
    {
        return response()->json([
            'data' => new PpmpItemResource($ppmpItem),
            'message' => "PPMP Item retrieved successfully."
        ], Response::HTTP_OK);
    }

    public function update(PpmpItemRequest $request, PpmpItem $ppmpItem)
    {
        $data = $request->all();
        $ppmpItem->update($data);

        return response()->json([
            'data' => new PpmpItemResource($ppmpItem),
            'message' => "PPMP Item updated successfully."
        ], Response::HTTP_OK);
    }

    public function destroy($id, Request $request)
    {
        $validate_pin = User::where('pin', $request->pin)->first();
        $ppmpItem = PpmpItem::where('item_id', $id)->first();

        // Check if the PPMP Item exists
        if (!$ppmpItem) {
            return response()->json([
                'message' => "PPMP Item not found."
            ], Response::HTTP_NOT_FOUND);
        }

        // Soft delete pivot records (activity_ppmp_item)
        foreach ($ppmpItem->activities as $activity) {
            ActivityPpmpItem::where('activity_id', $activity->id)
                ->where('ppmp_item_id', $ppmpItem->id)
                ->first()?->delete();
        }

        // Soft delete the PPMP Item
        $ppmpItem->delete();

        return response()->json([
            'data' => $ppmpItem,
            'message' => "PPMP Item deleted successfully."
        ], Response::HTTP_OK);
    }

    public function import(Request $request)
    {
        // Handle the import logic here
        // You can use a package like Maatwebsite Excel for importing Excel files
        // or handle CSV files directly using PHP's built-in functions.

        return response()->json([
            'message' => "Import functionality is not implemented yet."
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    public function search(Request $request)
    {
        $term = $request->input('search');
        $terms = $term ? explode(' ', $term) : [];

        $query = PpmpApplication::with([
            'user',
            'divisionChief',
            'budgetOfficer',
            'aopApplication',
            'ppmpItems' => function ($query) use ($terms) {
                $query->with([
                    'item' => function ($query) {
                        $query->with([
                            'itemUnit',
                            'itemCategory',
                            'itemClassification',
                            'itemSpecifications',
                        ]);
                    },
                    'procurementMode',
                    'itemRequest',
                    'activities',
                    'comments',
                    'ppmpSchedule',
                ])->search($terms);
            },
        ])->whereNull('deleted_at')->latest()->first();

        return response()->json([
            'data' => new PpmpApplicationResource($query),
            'message' => "PPMP Items retrieved successfully."
        ], Response::HTTP_OK);
    }
}
