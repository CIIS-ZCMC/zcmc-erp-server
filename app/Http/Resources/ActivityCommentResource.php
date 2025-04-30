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
            'aop_application_id' => $this->activity && $this->activity->applicationObjective ? $this->activity->applicationObjective->aop_application_id : null,
            'activity_comment_id' => $this->id,
            'activity_id' => $this->activity_id,
            'user_id' => $this->user_id,
            'name' => $this->user ? $this->user->name : null,
            'designation' => $this->user && $this->user->assignedArea && $this->user->assignedArea->designation ? $this->user->assignedArea->designation->name : null,
            'area' => $this->user && $this->user->assignedArea && method_exists($this->user->assignedArea, 'findDetails') && isset($this->user->assignedArea->findDetails()['details']['name']) ? $this->user->assignedArea->findDetails()['details']['name'] : null,
            'area_code' => $this->user && $this->user->assignedArea && method_exists($this->user->assignedArea, 'findDetails') && isset($this->user->assignedArea->findDetails()['details']['code']) ? $this->user->assignedArea->findDetails()['details']['code'] : null,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
