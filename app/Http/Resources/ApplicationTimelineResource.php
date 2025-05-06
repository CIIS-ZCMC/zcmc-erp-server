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
            "aop_application" => $this->aopApplication,
            "ppmp_application" => $this->ppmpApplication,
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
