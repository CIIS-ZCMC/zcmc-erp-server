<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'module_path' => $this->module_path,
            'employee_profile_id' => $this->whenLoaded('userNotification', function() {
                return $this->userNotification->isNotEmpty() ? $this->userNotification->first()->user_id : null;
            }),
            'seen' => $this->whenLoaded('userNotification', function() {
                return $this->userNotification->isNotEmpty() ? $this->userNotification->first()->seen : false;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
