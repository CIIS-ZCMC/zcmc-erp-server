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
            'cost' => $this->cost,
            'start_month' => $this->start_month,
            'end_month' => $this->end_month,
            'target' => new TargetResource($this->whenLoaded('target')),
            'resources' => ResourceResource::collection($this->whenLoaded('resources')),
            'responsible_people' => ResponsiblePersonResource::collection($this->whenLoaded('responsiblePeople')),
        ];
    }
}
