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
            'employee_profile_id' => $this->user_notification?->employee_profile_id,
            'seen' => $this->user_notification?->seen,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
