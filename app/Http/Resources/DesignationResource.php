<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignationResource extends JsonResource
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
            'umis_designation_id' => $this->umis_designation_id,
            'name' => $this->name,
            'label' => $this->name,
            'code' => $this->code,
            'probation' => $this->probation,
        ];
    }
}
