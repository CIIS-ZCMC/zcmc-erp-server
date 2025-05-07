<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeadlineRequest;
use App\Http\Resources\DeadlineResource;
use App\Models\Deadline;
use Illuminate\Http\Request;

class DeadlineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->has('type')) {
            $type = $request->type;

            if ($type == 'aop') {
                $deadlines = Deadline::whereNotNull('aop_deadline')->get();
            } elseif ($type == 'ppmp') {
                $deadlines = Deadline::whereNotNull('ppmp_deadline')->get();
            } else {
                return response()->json(['message' => 'Invalid type. Use "aop" or "ppmp".'], 400);
            }
        } else {

            $deadlines = Deadline::all();
        }

        return DeadlineResource::collection($deadlines);
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
    public function storeAopDeadline(DeadlineRequest $request)
    {
        $aop_deadline = $request->aop_deadline;
        $aop_start_date = $request->aop_start_date;

        if (!$aop_deadline) {
            return response()->json([
                'message' => 'A valid AOP deadline is required.'
            ], 422);
        }

        $year = date('Y', strtotime($aop_deadline));

        $exists = Deadline::whereYear('aop_deadline', $year)->exists();
        if ($exists) {
            return response()->json([
                'message' => "An AOP deadline for the year {$year} already exists."
            ], 409);
        }

        $deadline = Deadline::create([
            'aop_deadline' => $aop_deadline,
            'aop_start_date' => $aop_start_date,
        ]);

        return new DeadlineResource($deadline);
    }

    public function storePpmpDeadline(DeadlineRequest $request)
    {
        $ppmp_deadline = $request->ppmp_deadline;
        $ppmp_start_date = $request->ppmp_start_date;

        if (!$ppmp_deadline) {
            return response()->json([
                'message' => 'A valid PPMP deadline is required.'
            ], 422);
        }

        $year = date('Y', strtotime($ppmp_deadline));

        $exists = Deadline::whereYear('ppmp_deadline', $year)->exists();
        if ($exists) {
            return response()->json([
                'message' => "An PPMP deadline for the year {$year} already exists."
            ], 409);
        }

        $deadline = Deadline::create([
            'ppmp_deadline' => $ppmp_deadline,
            'ppmp_start_date' => $ppmp_start_date,
        ]);

        return new DeadlineResource($deadline);
    }

    public function updateAopDeadline(DeadlineRequest $request, $id)
    {
        $aop_deadline = $request->aop_deadline;
        $aop_start_date = $request->aop_start_date;

        if (!$aop_deadline) {
            return response()->json([
                'message' => 'A valid AOP deadline is required.'
            ], 422);
        }

        $year = date('Y', strtotime($aop_deadline));

       
        $exists = Deadline::whereYear('aop_deadline', $year)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "Another AOP deadline for the year {$year} already exists."
            ], 409);
        }

        $deadline = Deadline::findOrFail($id);

        $deadline->update([
            'aop_deadline' => $aop_deadline,
            'aop_start_date' => $aop_start_date,
        ]);

        return new DeadlineResource($deadline);
    }

    public function updatePpmpDeadline(DeadlineRequest $request, $id)
    {
        $ppmp_deadline = $request->ppmp_deadline;
        $ppmp_start_date = $request->ppmp_start_date;

        if (!$ppmp_deadline) {
            return response()->json([
                'message' => 'A valid PPMP deadline is required.'
            ], 422);
        }

        $year = date('Y', strtotime($ppmp_deadline));


        $exists = Deadline::whereYear('ppmp_deadline', $year)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "Another PPMP deadline for the year {$year} already exists."
            ], 409);
        }

        $deadline = Deadline::findOrFail($id);

        $deadline->update([
            'ppmp_deadline' => $ppmp_deadline,
            'ppmp_start_date' => $ppmp_start_date,
        ]);

        return new DeadlineResource($deadline);
    }

    /**
     * Display the specified resource.
     */
    public function show(Deadline $deadline)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deadline $deadline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deadline $deadline)
    {
        //
    }
}
