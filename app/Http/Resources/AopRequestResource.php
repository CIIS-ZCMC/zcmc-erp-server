<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class AopRequestResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'created_on' => $this->applicationTimeline->date_created ?? null, 
            'date_approved'=> $this->applicationTimeline->date_approved ?? null,
            'aop_application_uuid' => $this->aop_application_uuid,
            'status' => $this->status,
        ];
    }
}
