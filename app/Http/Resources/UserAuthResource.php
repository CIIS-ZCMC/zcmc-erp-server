<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $abilities = $this->session->abilities;

        return [
            'id' => $this->id,
            'umis_id' => $this->umis_id,
            'name' => $this->name,
            'email' => $this->email,
            'assignedArea' => new AssignedAreaResource($this->assignedArea),
            'meta' => [
                'permissions' => $abilities,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
