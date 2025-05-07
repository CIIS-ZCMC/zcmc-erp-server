<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AopRemarksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Format the response with emphasis on the remarks data
        return [
            'aop_application_id' => $this->id,
            'remarks' => $this->remarks,
            // Division Chief information
            'division_chief_id' => $this->division_chief_id,
            'division_chief_name' => $this->divisionChief ? $this->divisionChief->name : null,
            'division_chief_designation' => $this->divisionChief && $this->divisionChief->assignedArea && $this->divisionChief->assignedArea->designation ? $this->divisionChief->assignedArea->designation->name : null,
            'division_chief_area' => $this->divisionChief && $this->divisionChief->assignedArea && method_exists($this->divisionChief->assignedArea, 'findDetails') && isset($this->divisionChief->assignedArea->findDetails()['details']['name']) ? $this->divisionChief->assignedArea->findDetails()['details']['name'] : null,
            'division_chief_area_code' => $this->divisionChief && $this->divisionChief->assignedArea && method_exists($this->divisionChief->assignedArea, 'findDetails') && isset($this->divisionChief->assignedArea->findDetails()['details']['code']) ? $this->divisionChief->assignedArea->findDetails()['details']['code'] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
