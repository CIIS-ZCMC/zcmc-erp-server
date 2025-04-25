<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PpmpApplicationResource extends JsonResource
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
            'ppmp_application_uuid' => $this->ppmp_application_uuid,
            'ppmp_total' => $this->ppmp_total,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->user ? new UserResource($this->user) : null,
            'division_chief' => $this->division_chief ? new UserResource($this->division_chief) : null,
            'budget_officer' => $this->budget_officer ? new UserResource($this->budget_officer) : null,
            'aop_application' => $this->aop_application ? new AopApplicationResource($this->aop_application) : null,
            'ppmp_items' => PpmpItemResource::collection($this->ppmpItems) ?? [],
        ];
    }
}
