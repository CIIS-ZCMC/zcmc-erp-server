<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCategoryChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $label = $this->name;
        
        if($this->itemCategory){
            $label = $this->itemCategory->name.":".$label;
        }

        return [
            'id' => $this->id,
            'label' => $label, 
            'name' => $this->name, 
            'code' => $this->code,
            'description' => $this->description,
            'parent_category_id' => $this->itemCategory->id,
            'meta' => [
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ]
        ];
    }
}
