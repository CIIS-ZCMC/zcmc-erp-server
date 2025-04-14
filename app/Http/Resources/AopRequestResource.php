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
            'date_created' => $this->created_at,
            'date_approved' => $this->whenLoaded('applicationTimeline', function () {
                $latestTimeline = $this->applicationTimeline->sortByDesc('created_at')->first();
                return $latestTimeline ? $latestTimeline->date_approved : null;
            }),
            'aop_application_uuid' => $this->aop_application_uuid,
            'status' => $this->status,
        ];
    }
}
