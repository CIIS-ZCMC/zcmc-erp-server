<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponsiblePersonResource extends JsonResource
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
            'division' => new DivisionResource($this->whenLoaded('division')),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'section' => new SectionResource($this->whenLoaded('section')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            // 'designation' => new DesignationResource($this->whenLoaded('designation')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
