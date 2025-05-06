<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApplicationTimeline;
use App\Http\Resources\ApplicationTimelineResource;
use App\Models\AopApplication;

class ApplicationTimelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $applicationTimelines = ApplicationTimeline::with(
            [
                'aopApplication'
            ]
        )->get();

        return response()->json([
            'message' => 'Application Timelines retrieved successfully',
            'data' => ApplicationTimelineResource::collection($applicationTimelines),
        ]);
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
        $aopApplicationTimeline = AopApplication::with(
            [
                'user',
                'user.designation',
                'divisionChief',
                'divisionChief.designation',
                'planningOfficer',
                'planningOfficer.designation',
                'mccChief',
                'mccChief.designation',
                'applicationTimelines',
                'applicationTimelines.user',
                'applicationTimelines.user.designation',
                'applicationObjectives.activities.comments'
            ]
        )->where('id', $id)->first();
        
        if (!$aopApplicationTimeline) {
            return response()->json([
                'message' => 'Application not found',
            ], 404);
        }
    
        return response()->json([
            'message' => 'Application Timeline retrieved successfully',
            'data' => ApplicationTimelineResource::make($aopApplicationTimeline),
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
