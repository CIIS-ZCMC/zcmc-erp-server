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
        $id = null;
        $area_id = null;
        $is_head = null;

        if ($this->division_id && $this->whenLoaded('division') && $this->division) {
            $name = $this->division->name;
            $type = 'division';
            $id = $this->division_id;
            $area_id = $this->division->area_id;
            $is_head = isset($this->division->head_id) && $this->division->head_id == $this->user_id;
        } elseif ($this->department_id && $this->whenLoaded('department') && $this->department) {
            $name = $this->department->name;
            $type = 'department';
            $id = $this->department_id;
            $area_id = $this->department->area_id;
            $is_head = isset($this->department->head_id) && $this->department->head_id == $this->user_id;
        } elseif ($this->section_id && $this->whenLoaded('section') && $this->section) {
            $name = $this->section->name;
            $type = 'section';
            $id = $this->section_id;
            $area_id = $this->section->area_id;
            $is_head = isset($this->section->head_id) && $this->section->head_id == $this->user_id;
        } elseif ($this->unit_id && $this->whenLoaded('unit') && $this->unit) {
            $name = $this->unit->name;
            $type = 'unit';
            $id = $this->unit_id;
            $area_id = $this->unit->area_id;
            $is_head = isset($this->unit->head_id) && $this->unit->head_id == $this->user_id;
        }

        return [
            'id' => $id,
            'area_id' => $area_id,
            'name' => $name,
            'type' => $type,
            'is_head' => $is_head
        ];
    }
}
