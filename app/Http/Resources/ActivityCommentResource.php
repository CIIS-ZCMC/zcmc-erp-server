<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'activity_comment_id' => $this->id,
            'activity_id' => $this->activity_id,
            'user_id' => $this->user_id,
            'name' => $this->user->name,
            'designation' => $this->user->assignedArea->designation->name,
            'area' => $this->user->assignedArea->findDetails()['details']['name'],
            'area_code' => $this->user->assignedArea->findDetails()['details']['code'],
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
