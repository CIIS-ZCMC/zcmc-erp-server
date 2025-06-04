<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowObjectiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $data = [];

        // Add essential activity attributes
        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->name !== null) {
            $data['activity_name'] = $this->name;
        }

        if ($this->is_reviewed !== null) {
            $data['is_reviewed'] = $this->is_reviewed;
        }

        if ($this->date_updated !== null) {
            $data['reviewed_date'] = $this->date_updated;
        }

        if ($this->start_month !== null) {
            $data['start_month'] = $this->start_month;
        }

        if ($this->end_month !== null) {
            $data['end_month'] = $this->end_month;
        }

        // Add target if it exists - simplified
        if ($this->target) {
            $targetData = [];

            if ($this->target->first_quarter !== null) {
                $targetData['q1'] = $this->target->first_quarter;
            }

            if ($this->target->second_quarter !== null) {
                $targetData['q2'] = $this->target->second_quarter;
            }

            if ($this->target->third_quarter !== null) {
                $targetData['q3'] = $this->target->third_quarter;
            }

            if ($this->target->fourth_quarter !== null) {
                $targetData['q4'] = $this->target->fourth_quarter;
            }

            if (!empty($targetData)) {
                $data['target'] = $targetData;
            }
        }

        // Add responsible people with minimal info
        if ($this->responsiblePeople && $this->responsiblePeople->isNotEmpty()) {
            $data['responsible_people'] = $this->responsiblePeople->map(function ($responsiblePerson) {
                $personData = [];

                // Only include name from user
                if ($responsiblePerson->user && isset($responsiblePerson->user->name)) {
                    $personData['name'] = $responsiblePerson->user->name;
                    $personData['designation'] = $responsiblePerson->user->assignedArea->designation->name;
                }

                // Only include designation name
                if ($responsiblePerson->designation && isset($responsiblePerson->designation->name)) {
                    $personData['designation'] = $responsiblePerson->designation->name;
                }

                // For areas, include only relevant names and sectors
                // Division information
                if ($responsiblePerson->division && isset($responsiblePerson->division->name)) {
                    $personData['name'] = $responsiblePerson->division->name;

                    if (isset($responsiblePerson->division->sector)) {
                        $personData['sector'] = $responsiblePerson->division->sector;
                    }
                }

                // Department information (if no division)
                elseif ($responsiblePerson->department && isset($responsiblePerson->department->name)) {
                    $personData['name'] = $responsiblePerson->department->name;

                    if (isset($responsiblePerson->department->sector) && !isset($personData['sector'])) {
                        $personData['sector'] = $responsiblePerson->department->sector;
                    }
                }

                // Section information (if no division/department)
                elseif ($responsiblePerson->section && isset($responsiblePerson->section->name)) {
                    $personData['name'] = $responsiblePerson->section->name;

                    if (isset($responsiblePerson->section->sector) && !isset($personData['sector'])) {
                        $personData['sector'] = $responsiblePerson->section->sector;
                    }
                }

                // Unit information (if no division/department/section)
                elseif ($responsiblePerson->unit && isset($responsiblePerson->unit->name)) {
                    $personData['name'] = $responsiblePerson->unit->name;

                    if (isset($responsiblePerson->unit->sector) && !isset($personData['sector'])) {
                        $personData['sector'] = $responsiblePerson->unit->sector;
                    }
                }

                return $personData;
            })->filter(function ($person) {
                // Remove any empty entries
                return !empty($person);
            });

            // Only include responsible_people if there's at least one valid entry
            if ($data['responsible_people']->isEmpty()) {
                unset($data['responsible_people']);
            }
        }

        // Add resources if they exist and not empty
        if ($this->resources && $this->resources->isNotEmpty()) {
            // Create a flat array of resources
            $resourcesList = [];

            foreach ($this->resources as $resource) {
                // Skip if resource has no valid data
                if (!$resource->item || !isset($resource->item->id)) {
                    continue;
                }

                $resourceItem = [
                    'id' => $resource->id,
                ];

                // Resource requirements as an object (not an array)
                $requirements = [];

                // Add item name if available
                if (isset($resource->item->name)) {
                    $requirements['item_name'] = $resource->item->name;
                }

                // Add quantity if available
                if ($resource->quantity !== null) {
                    $requirements['quantity'] = $resource->quantity;
                }

                // Add cost information from item's estimated budget
                if (isset($resource->item->estimated_budget)) {
                    $requirements['unit_cost'] = $resource->item->estimated_budget;

                    // Calculate and add total cost if quantity is available
                    if ($resource->quantity !== null) {
                        $requirements['total_cost'] = $resource->item->estimated_budget * $resource->quantity;
                    }
                }

                // Only add resource_requirements if it has data
                if (!empty($requirements)) {
                    $resourceItem['resource_requirements'] = $requirements;
                }

                // Add expense class
                if ($resource->expense_class !== null) {
                    $resourceItem['expense_class'] = $resource->expense_class;
                }

                // Add type of resource at the top level
                if ($resource->item && $resource->item->itemCategory) {
                    $resourceItem['type_of_resource'] = $resource->item->itemCategory->name;
                }

                // Add mode of procurement
                if ($resource->purchaseType) {
                    $resourceItem['mode_of_procurement'] = $resource->purchaseType->description;
                }

                // Add is_gad field if it exists
                if (isset($resource->is_gad)) {
                    $resourceItem['is_gad'] = (bool)$resource->is_gad;
                }

                // Only add resource if it has meaningful data
                if (isset($resourceItem['resource_requirements'])) {
                    $resourcesList[] = $resourceItem;
                }
            }

            // Add to data
            if (!empty($resourcesList)) {
                $data['resources'] = $resourcesList;
            }
        }

        // Add comments if they exist and not empty
        if ($this->comments && $this->comments->isNotEmpty()) {
            $data['comments'] = $this->comments;
        }

        return $data;
    }
}
