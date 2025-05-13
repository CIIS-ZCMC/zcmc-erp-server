<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ItemRequest;
use App\Models\ItemSpecification;
use App\Models\PpmpApplication;
use Illuminate\Http\Request;
use App\Services\ItemService;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemRequestControlller extends Controller
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
        $activities = Activity::find($request->activity_id);

        if (!$activities) {
            return response()->json([
                'message' => 'Activity not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $item_request = [
            'activity_id' => $activities->id,
            'name' => $request->name,
            'code' => $request->code,
            'variant' => $request->variant,
            'estimated_budget' => $request->estimated_budget,
            'item_unit_id' => $request->item_unit_id,
            'item_category_id' => $request->item_category_id,
            'item_classification_id' => $request->item_classification_id,
        ];

        $itemRequest = ItemRequest::create($item_request);

        $item_specifications = $request->specifications;
        foreach ($item_specifications as $specification) {
            ItemSpecification::create([
                "item_request_id" => $itemRequest->id,
                "description" => $specification['description']
            ]);
        }
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

    public function approval(Request $request)
    {
        // $services = app(ItemService::class);

        // $data = ItemRequest::findOrFail($request->id);
        // $data->status = $request->status;
        // $data->reason = $request->reason;
        // $data->save();

        // if ($request->status === 'approved') {
        //     $details = [
        //         'name' => $data->name,
        //         'code' => $data->code,
        //         'variant' => $data->variant,
        //         'estimated_budget' => $data->estimated_budget,
        //         'item_unit_id' => $data->item_unit_id,
        //         'item_category_id' => $data->item_category_id,
        //         'item_classification_id' => $data->item_classification_id,
        //     ];

        //     $services->store($details);
        // }
    }
}
