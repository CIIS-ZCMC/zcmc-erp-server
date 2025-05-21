<?php

namespace App\Http\Controllers;

use App\Models\ActivityComment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ActivityCommentController extends Controller
{
   
    public function index()
    {
        return ActivityComment::when(request('activity_id'), function ($query) {
                $query->where('activity_id', request('activity_id'));
            })
            ->paginate(request('per_page', 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_id' => 'required|integer|exists:activities,id',
            'content' => 'required|string|max:500',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        return ActivityComment::create($validated);
    }

    public function show(ActivityComment $activityComment)
    {
        return $activityComment;
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