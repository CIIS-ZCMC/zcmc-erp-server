<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'name' => $this->name,
            'is_gad_related' => $this->is_gad_related,
            'is_reviewed' => $this->is_reviewed,
            'is_reviewed_date' => $this->date_updated,
            'cost' => $this->cost,
            'start_month' => $this->start_month ? \Carbon\Carbon::parse($this->start_month)->format('Y-m') : null,
            'end_month' => $this->end_month ? \Carbon\Carbon::parse($this->end_month)->format('Y-m') : null,
            'target' => new TargetResource($this->whenLoaded('target')),
            'resources' => ResourceResource::collection($this->whenLoaded('resources')),
            'responsible_people' => ResponsiblePersonResource::collection($this->whenLoaded('responsiblePeople')),
            'comments' => ActivityCommentResource::collection($this->whenLoaded('comments')) ?? [],

        ];
    }
}
