<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $label = $this->name;
        $subcategory = [];
        $parentCategory = null;

        if(count($this->itemCategories) > 0){
            $subcategory = ItemCategoryChildResource::collection($this->itemCategories);
        }

        if($this->itemCategory){
            $parentCategory = new ItemCategoryParentResource($this->itemCategory);
            $label = $label.":".$this->itemCategory->name;
        }

        return [
            'id' => $this->id,
            'name' => $label,
            'code' => $this->code,
            'description' => $this->description,
            'parent_category' => $parentCategory,
            'sub_category' => $subcategory,
            'meta' => [
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
