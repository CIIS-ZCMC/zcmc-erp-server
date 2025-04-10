<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemSpecificationChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $label = $this->description;
        
        if($this->itemSpecification){
            $label = $this->itemSpecification->description.":".$label;
        }

        return [
            'id' => $this->id,
            'description' => $this->description,
            'parent_specification_id' => $this->itemSpecification->id,
            'meta' => [
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
