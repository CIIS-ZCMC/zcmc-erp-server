<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ResponsiblePeopleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Collect all areas from responsible people
        $areas = $this->collection->flatMap(function ($r) {
            return collect([
                $r->division,
                $r->department,
                $r->section,
                $r->unit,
            ]);
        })->filter()
            ->unique(fn($area) => $area?->getMorphClass() . '-' . $area?->id)
            ->values();

        // Map each area to its appropriate resource
        $areaResources = $areas->map(function ($area) {
            return match (get_class($area)) {
                \App\Models\Division::class => new DivisionResource($area),
                \App\Models\Department::class => new DepartmentResource($area),
                \App\Models\Section::class => new SectionResource($area),
                \App\Models\Unit::class => new UnitResource($area),
                default => null,
            };
        })->filter();

        return [
            'activity_id' => $this->first()?->activity_id,
            'activity_uuid' => $this->first()?->activity_uuid,

            'areas' => $areaResources,

            'users' => $this->map(fn($r) => $r->user)->filter()->unique('id')->values()->map(fn($u) => new UserResource($u)),

            'designations' => $this->map(fn($r) => $r->designation)->filter()->unique('id')->values()->map(fn($d) => new DesignationResource($d)),

            'created_at' => $this->first()?->created_at,
            'updated_at' => $this->first()?->updated_at,
        ];
    }
}
