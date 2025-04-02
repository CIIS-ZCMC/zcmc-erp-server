<?php

namespace App\Http\Controllers;

use App\Http\Resources\AopApplicationResource;
use App\Models\AopApplication;
use Illuminate\Http\Request;

class AopApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aopApplications = AopApplication::query()
            ->with([
                'applicationObjectives.functionObjective.function',
                'applicationObjectives.functionObjective.objective',
                'applicationObjectives.othersObjective',
                'applicationObjectives.activities.target',
                'applicationObjectives.activities.resources',
                'applicationObjectives.activities.responsiblePeople.user'
            ])
            ->get();
        return AopApplicationResource::collection($aopApplications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(AopApplication $aopApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AopApplication $aopApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AopApplication $aopApplication)
    {
        //
    }
}
