<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "image" => $this->image !== null? env("SERVER_DOMAIN").$this->image: null,
            "estimated_budget" => $this->estimated_budget,
            "unit" => $this->item_unit_id == null? null: $this->itemUnit->code,
            "category" => $this->item_category_id == null? null: $this->itemCategory->code,
            "classification" => $this->item_classification_id !== null? $this->itemClassification->code : null,
            "item_unit" => $this->item_unit_id == null? null:  new ItemUnitResource($this->itemUnit),
            "variant" => $this->variant_id == null? null:  new VariantResource($this->variant),
            "snomed" => $this->snomed_id == null? null:  new SnomedResource($this->snomed),
            "item_category" => $this->item_category_id == null? null: new ItemCategoryResource($this->itemCategory),
            "item_classification" => $this->item_classification_id !== null? null:$this->itemClassification,
            "item_specifications" => ItemSpecificationChildResource::collection($this->itemSpecifications),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
