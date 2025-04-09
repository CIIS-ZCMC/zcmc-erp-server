<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationTimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "aop_application_id" => $this->aop_application_id,
            "aop_application_uuid" => $this->aopApplication->aop_application_uuid,
            "ppmp_application_id" => $this->ppmp_application_id,
            "approver_id" => $this->user->id,
            "approved_by" => $this->user->name,
            "status" => $this->status,
            "remarks" => $this->remarks,
            "date_created" => $this->date_created,
            "date_approved" => $this->date_approved,
            "date_returned" => $this->date_returned,
        ];
    }
}
