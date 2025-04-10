<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManageAopRequestResource extends JsonResource
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
            'aop_application_uuid' => $this->aop_application_uuid,
            'mission' => $this->mission,
            'status' => $this->status,
            'has_discussed' => $this->has_discussed,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'objectives' => $this->objectives,
            'success_indicator' => $this->successIndicator,
        ];
    }
}
