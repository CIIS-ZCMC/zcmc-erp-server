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
    protected $activityUuid;
    protected $quantity;

    public function __construct($resource, $activityUuid = null, $quantity = null)
    {
        parent::__construct($resource);
        $this->activityUuid = $activityUuid;
        $this->quantity = $quantity;
    }
    public function toArray(Request $request): array
    {
        return [
            "parentId" => $this->activityUuid,
            "aop_quantity" => $this->quantity,
            "id" => $this->id,
            "label" => $this->name,
            "name" => $this->name,
            "code" => $this->code,
            "variant" => $this->variant,
            "image" => $this->image !== null ? env("SERVER_DOMAIN") . $this->image : null,
            "estimated_budget" => $this->estimated_budget,
            "unit" => $this->item_unit_id == null ? null : $this->itemUnit->code,
            "category" => $this->item_category_id == null ? null : $this->itemCategory->code,
            "classification" => $this->item_classification_id !== null ? $this->itemClassification->code : null,
            "item_unit" => $this->item_unit_id == null ? null : new ItemUnitResource($this->itemUnit),
            "item_category" => $this->item_category_id == null ? null : new ItemCategoryResource($this->itemCategory),
            "item_classification" => $this->item_classification_id !== null ? null : $this->itemClassification,
            "item_specifications" => ItemSpecificationChildResource::collection($this->itemSpecifications),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
