<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

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
            'id' => $this->id,
            'activity_uuid' => $this->activity_uuid,
            'activity_code' => $this->activity_code,
            'activity_name' => $this->name,
            "is_gad_related" => $this->is_gad_related,
            "is_reviewed" => $this->is_reviewed,
            "cost" => $this->cost,
            "start_month" => $this->start_month,
            "end_month" => $this->end_month,
            "comments" => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at,
                        'commented_by' => $comment->user ? $comment->user->name : null,
                        'commented_by_id' => $comment->user ? $comment->user->id : null
                    ];
                });
            }),
        ];
    }
}
