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
        $applicationObjectives = $this->applicationObjectives;

        $type_of_function = $applicationObjectives
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


        $application_objective = $applicationObjectives->map(function ($appObj) use ($type_of_function) {
            return [
                'id' => $appObj->id,
                'aop_application_id' => $appObj->aop_application_id,
                'objective_id' => $appObj->objective_id,
                'success_indicator_id' => $appObj->success_indicator_id,
                'deleted_at' => $appObj->deleted_at,
                'created_at' => $appObj->created_at,
                'updated_at' => $appObj->updated_at,
                'function' => $type_of_function->filter(function ($function) use ($appObj) {
                    return $function['id'] === $appObj->objective->type_of_function_id;
                })->values(),
                'activities' => ActivityResource::collection($appObj->activities),
            ];
        });

        return [
            'id' => $this->id,
            'application_objectives' => $application_objective,
        ];
    }
}
