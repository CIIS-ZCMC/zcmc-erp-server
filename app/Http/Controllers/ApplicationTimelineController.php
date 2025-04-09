<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationTimeline;
use App\Http\Resources\ApplicationTimelineResource;

class ApplicationTimelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $applicationTimeline = ApplicationTimeline::with(
            [
                'aopApplication'
            ]
        )->where('id', $id)->first();

        return response()->json([
            'message' => 'Application Timeline retrieved successfully',
            'data' => ApplicationTimelineResource::make($applicationTimeline),
        ]);
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
