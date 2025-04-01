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
            'aop_application' => $this->aop_application,
            'user' => $this->user,
            'division_chief' => $this->division_chief,
            'budget_officer' => $this->budget_officer,
            'ppmp_application_uuid' => $this->ppmp_application_uuid,
            'ppmp_total' => $this->ppmp_total,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
