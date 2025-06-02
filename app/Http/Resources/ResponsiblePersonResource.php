<?php

namespace App\Http\Resources;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Division;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
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
            'designation' => new DesignationResource($this->whenLoaded('designation')),
            'options' => [
                'users' => UserResource::collection(User::all()),
                'divisions' => DivisionResource::collection(Division::all()),
                'departments' => DepartmentResource::collection(Department::all()),
                'sections' => SectionResource::collection(Section::all()),
                'units' => UnitResource::collection(Unit::all()),
                'designations' => DesignationResource::collection(Designation::all()),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
