<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemSpecificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $label = $this->description;
        $subspecification = [];
        $parentSpecification = null;

        if(count($this->itemSpecifications) > 0){
            $subspecification = ItemSpecificationChildResource::collection($this->itemSpecifications);
        }

        if($this->itemSpecification){
            $parentSpecification = new ItemSpecificationParentResource($this->itemSpecification);
            $label = $label.":".$this->itemSpecification->description;
        }

        return [
            'id' => $this->id,
            'label' => $label,
            'description' => $this->description,
            'parent_specification' => $parentSpecification,
            'sub_specifications' => $subspecification,
            'meta' => [
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
