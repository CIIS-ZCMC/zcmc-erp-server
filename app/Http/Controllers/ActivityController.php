<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\CommentsPerActivityResource;
use App\Models\Activity;
use App\Models\AopApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use User;

class ActivityController extends Controller
{
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // $request->validate([
        //     'application_objective_id' => 'required|integer',
        // ]);

        $aop = AopApplication::latest()->first();

        // Fetch activities based on the application objective ID
        $data = Activity::with([
            'applicationObjective' => function ($query) use ($aop) {
                $query->where('aop_application_id', $aop->id);
            },
        ])->whereNull(columns: 'deleted_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => ActivityResource::collection($data),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem()
            ],
            'links' => [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl()
            ],
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Activity $activity)
    {
        $activity->with([
            'target',
            'resources',
            'responsiblePeople',
            'ppmpItems' => function ($query) {
                $query->whereNull('ppmp_items.deleted_at');
            }
        ])->first();

        return response()->json([
            'data' => new ActivityResource($activity),
            'message' => 'Activity retrieved successfully'
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Activity $activity)
    {
        //
    }

    public function destroy(Activity $activity)
    {
        //
    }

    public function commentsPerActivity()
    {
        $activity_comments = Activity::with(['commenbts.user'])->paginate(15);

        return response()->json([
            "data" => CommentsPerActivityResource::collection($activity_comments)
        ]);
    }

    /**
     * Mark the specified activity as reviewed.
     *
     * @param int $activity_id
     * @return JsonResponse
     *
     * Last edited by Micah Mustaham
     */
    public function markAsReviewed(int $activity_id): JsonResponse
    {
        // Find the activity by ID
        $activity = Activity::find($activity_id);

        // Return error if activity not found
        if (!$activity) {
            return response()->json([
                "message" => "Activity not found"
            ], Response::HTTP_NOT_FOUND);
        }

        // Mark the activity as reviewed
        $activity->is_reviewed = true;
        try {
            $activity->save();
        } catch (\Throwable $th) {
            // Return error response if saving fails
            return response()->json([
                "message" => "Failed to mark activity as reviewed"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success response
        return response()->json([
            "data" => new ActivityResource($activity),
            "message" => "Activity marked as reviewed"
        ], Response::HTTP_OK);
    }

    /**
     * Mark the specified activity as reviewed.
     *
     * @param int $activity_id
     * @return JsonResponse
     *
     * Last edited by Micah Mustaham
     */
    public function markAsUnreviewed(int $activity_id): JsonResponse
    {
        // Find the activity by ID
        $activity = Activity::find($activity_id);

        // Return error if activity not found
        if (!$activity) {
            return response()->json([
                "message" => "Activity not found"
            ], Response::HTTP_NOT_FOUND);
        }

        // Mark the activity as reviewed
        $activity->is_reviewed = false;
        try {
            $activity->save();
        } catch (\Throwable $th) {
            // Return error response if saving fails
            return response()->json([
                "message" => "Failed to mark activity as reviewed"
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return success response
        return response()->json([
            "data" => new ActivityResource($activity),
            "message" => "Activity marked as reviewed"
        ], Response::HTTP_OK);
    }
}
