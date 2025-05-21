<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityComment;
use App\Models\AopApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ActivityCommentResource;
use App\Http\Resources\CommentsPerActivityResource;
use App\Models\User;

class ActivityCommentController extends Controller
{

    private $is_development;
    private $module = 'activity-comments';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanActivityCommentData(array $data): array
    {
        $cleanData = [];

        if (isset($data['comment'])) {
            $cleanData['comment'] = strip_tags($data['comment']);
        }

        return $cleanData;
    }

    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata = ["methods" => ["GET, POST, PUT, DELETE"]];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?activity_comment_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $metadata;
        }

        if ($method === 'put') {
            $metadata = ["methods" => ["PUT"]];

            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2"
                ];
                $metadata['fields'] = ["comment"];
            }

            return $metadata;
        }

        $metadata = ['methods' => ["GET, PUT, DELETE"]];

        if ($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];

            $metadata["fields"] =  ["comment"];
        }

        return $metadata;
    }
    public function index(Request $request)
    {
        $aop_application_id = $request->query('aop_application_id');

        if (!$aop_application_id) {
            return response()->json([
                'message' => 'AOP Application ID is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get the AOP application with nested relationships
        $aopApplication = AopApplication::with([
            'applicationObjectives.activities.comments' => function ($query) {
                $query->with(['user.assignedArea.designation', 'activity.applicationObjective'])
                    ->orderBy('created_at', 'desc');
            }
        ])->find($aop_application_id);

        if (!$aopApplication) {
            return response()->json([
                'message' => 'AOP Application not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Extract and flatten all comments using flatMap
        $allComments = $aopApplication->applicationObjectives->flatMap(function ($objective) {
            return $objective->activities->flatMap(function ($activity) {
                return $activity->comments;
            });
        })->sortByDesc('created_at')->values();

        if ($allComments->isEmpty()) {
            return response()->json([
                'message' => 'No comments found for this AOP application'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "message" => "AOP Application Comments retrieved successfully",
            "data" => ActivityCommentResource::collection($allComments),
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = User::find($request->user()->id);
        $validated = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'comment' => 'required|string',
        ]);

        $activity = Activity::findOrFail($validated['activity_id']);

        $activity->comments()->create([
            'comment' => $validated['comment'],
            'user_id' => $user->id,
        ]);

        $comment = $activity->comments()->latest()->first();

        return response()->json([
            "data" => new ActivityCommentResource($comment),
            "message" => 'Comment added successfully'
        ], 201);
    }

    public function show($activity_id)
    {
        $activity = Activity::with(['comments.user.assignedArea'])
            ->find($activity_id);

        if (!$activity || !$activity->comments->count()) {
            return response()->json([
                'message' => 'No activity comments found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new CommentsPerActivityResource($activity),
            'message' => 'Comments retrieved successfully'
        ], Response::HTTP_OK);
    }
    public function update(Request $request, ActivityComment $activityComment)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        $activityComment->update($validated);
        return $activityComment;
    }

    public function destroy(ActivityComment $activityComment)
    {
        $activityComment->delete();
        return response()->noContent();
    }
}
