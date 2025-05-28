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

        return [
            'id' => $this->id,
            'application_objectives' => $this->applicationObjectives->map(function ($appObj) {
                $type_of_function = $appObj->objective->typeOfFunction;
                $objective = $appObj->objective;
                $success_indicator = $appObj->successIndicator;

                return [
                    'id' => $appObj->id,
                    'function_type' => [
                        'id' => $type_of_function->id,
                        'name' => $type_of_function->code,
                        'label' => $type_of_function->code,
                        'type' => $type_of_function->type,
                    ],
                    'objective' => [
                        'id' => $objective->id,
                        'name' => $objective->code,
                        'label' => $objective->code,
                        'description' => $objective->description,
                        'type_function_id' => $objective->type_of_function_id
                    ],
                    'success_indicator' => [
                        'id' => $success_indicator->id,
                        'name' => $success_indicator->code,
                        'label' => $success_indicator->code,
                        'description' => $success_indicator->description,
                        'objective_id' => $success_indicator->objective_id,
                    ],
                    'activity' => ActivityResource::collection($appObj->activities),
                ];
            }),
        ];
    }
}
