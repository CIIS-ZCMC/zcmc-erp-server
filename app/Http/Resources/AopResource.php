<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type_of_functions = $this->applicationObjectives
            ->groupBy(fn($item) => $item->objective->type_of_function_id)
            ->map(function ($items) {
                $typeOfFunction = $items->first()->objective->typeOfFunction;

                return [
                    'id' => $typeOfFunction->id,
                    'label' => $typeOfFunction->type,
                    'name' => $typeOfFunction->type,
                    'code' => $typeOfFunction->code,
                    'type' => $typeOfFunction->type,
                    'objectives' => $items->map(function ($obj) {
                        return [
                            'id' => $obj->objective->id,
                            'label' => $obj->objective->code,
                            'name' => $obj->objective->code,
                            'code' => $obj->objective->code,
                            'description' => $obj->objective->description,
                            'success_indicators' => $obj->objective->successIndicators->map(function ($si) {
                                return [
                                    'id' => $si->id,
                                    'label' => $si->code,
                                    'name' => $si->code,
                                    'code' => $si->code,
                                    'description' => $si->description,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values();


        return [
            'id' => $this->id,
            // 'sector_id' => $this->sector_id,
            // 'sector' => $this->sector,
            // // 'year' => $this->year,
            // 'status' => $this->status,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'type_of_functions' => $type_of_functions,
        ];
    }
}
