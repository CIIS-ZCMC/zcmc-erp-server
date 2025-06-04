<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AopApplicationResource extends JsonResource
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
            // 'user' => new UserResource($this->whenLoaded('user')), // Assuming you have a UserResource
            // 'division_chief' => new UserResource($this->whenLoaded('divisionChief')),
            // 'mcc_chief' => new UserResource($this->whenLoaded('mccChief')),
            // 'planning_officer' => new UserResource($this->whenLoaded('planningOfficer')),
            'aop_application_id' => $this->id,
            'aop_application_uuid' => $this->aop_application_uuid,
            'mission' => $this->mission,
            'status' => $this->status,
            'has_discussed' => $this->has_discussed,
            'remarks' => $this->remarks,
            'application_objectives' => ApplicationObjectiveResource::collection($this->whenLoaded('applicationObjectives')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
