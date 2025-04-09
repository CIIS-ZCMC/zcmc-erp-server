<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogDescriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "title" => $this->title,
            "code" => $this->code,
            "description" => $this->description,
            "meta" => [
                "created_at" => $this->created_at,
                "updated_at"=> $this->updated_at
            ]
        ];
    }
}
