<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentsPerActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'activity_id' => $this->id,
            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'user_id' => $comment->user_id,
                        'name' => $comment->user ? $comment->user->name : null,
                        'designation' => $comment->user && $comment->user->assignedArea && $comment->user->assignedArea->designation ? $comment->user->assignedArea->designation->name : null,
                        'area' => $comment->user && $comment->user->assignedArea ? $comment->user->assignedArea->findDetails()['details']['name'] : null,
                        'area_code' => $comment->user && $comment->user->assignedArea ? $comment->user->assignedArea->findDetails()['details']['code'] : null,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at
                    ];
                });
            }),
        ];
    }
}
