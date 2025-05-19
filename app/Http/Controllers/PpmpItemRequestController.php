<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ItemRequest;
use App\Models\ItemSpecification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $activity = json_decode($request->activity, true);
        $variant = json_decode($request->variant, true);
        $expense_class = json_decode($request->expense_class, true);
        $item_unit = json_decode($request->unit, true);
        $item_category = json_decode($request->category, true);
        $item_classification = json_decode($request->classification, true);
        $item_specifications = json_decode($request->specifications, true);

        $activities = Activity::find($activity['id']);

        if (!$activities) {
            return response()->json([
                'message' => 'Activity not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $item_request = [
            'activity_id' => $activities->id,
            'name' => $request->item_name,
            'variant' => $variant['name'],
            'estimated_budget' => $request->estimated_budget,
            'item_unit_id' => $item_unit['id'],
            'item_category_id' => $item_category['id'],
            'item_classification_id' => $item_classification['id'],
        ];

        $itemRequest = ItemRequest::create($item_request);

        foreach ($item_specifications as $specification) {
            ItemSpecification::create([
                "item_request_id" => $itemRequest->id,
                "item_specification_id " => $specification['id'],
                'description' => $specification['value'],
            ]);
        }

        return response()->json([
            'data' => $itemRequest,
            'message' => 'Item request created successfully'
        ], Response::HTTP_CREATED);
    }

    /** 
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
