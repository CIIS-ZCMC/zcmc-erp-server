<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedAreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $name = null;
        $type = null;

        if ($this->division_id && $this->whenLoaded('division') && $this->division) {
            $name = $this->division->name;
            $type = 'division';
        } elseif ($this->department_id && $this->whenLoaded('department') && $this->department) {
            $name = $this->department->name;
            $type = 'department';
        } elseif ($this->section_id && $this->whenLoaded('section') && $this->section) {
            $name = $this->section->name;
            $type = 'section';
        } elseif ($this->unit_id && $this->whenLoaded('unit') && $this->unit) {
            $name = $this->unit->name;
            $type = 'unit';
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'type' => $type,
        ];
    }
}
