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
            // 'user' => new UserResource($this->whenLoaded('user')), // Assuming you have a UserResource
            'division' => new DivisionResource($this->whenLoaded('division')), // Assuming you have a DivisionResource
            'department' => new DepartmentResource($this->whenLoaded('department')), // Assuming you have a DepartmentResource
            'section' => new SectionResource($this->whenLoaded('section')), // Assuming you have a SectionResource
            'unit' => new UnitResource($this->whenLoaded('unit')), // Assuming you have a UnitResource
            // 'designation' => new DesignationResource($this->whenLoaded('designation')), // Assuming you have a DesignationResource
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
