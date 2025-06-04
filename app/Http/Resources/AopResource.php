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
        return [
            'id' => $this->id,
            'application_objectives' => $this->applicationObjectives->map(function ($appObj) {
                $objective = $appObj->objective;
                $success_indicator = $appObj->successIndicator;
                $type_of_function = $appObj->objective->typeOfFunction;
                $other_objective = $appObj->otherObjective;
                $other_success_indicator = $appObj->otherSuccessIndicator;

                $option_objectives = $type_of_function->objectives;

                return [
                    'id' => $appObj->id,
                    'function_type' => $type_of_function === null ? [] : [
                        'id' => $type_of_function->id ?? null,
                        'name' => ucfirst($type_of_function->type) ?? null,
                        'label' => ucfirst($type_of_function->type) ?? null,
                        'code' => $type_of_function->code ?? null,
                        'objectives' => $option_objectives->map(function ($option_obj) {
                            return [
                                'id' => $option_obj->id ?? null,
                                'name' => $option_obj->code ?? null,
                                'label' => $option_obj->code ?? null,
                                'code' => $option_obj->code ?? null,
                                'description' => $option_obj->description ?? null,
                                'type_function_id' => $option_obj->type_of_function_id ?? null,
                                'success_indicators' => $option_obj->successIndicators->map(function ($option_si) {
                                    return [
                                        'id' => $option_si->id ?? null,
                                        'name' => $option_si->code ?? null,
                                        'label' => $option_si->code ?? null,
                                        'code' => $option_si->code ?? null,
                                        'description' => $option_si->description ?? null,
                                        'objective_id' => $option_si->objective_id ?? null,
                                    ];
                                })
                            ];
                        })
                    ],
                    'objective' => $objective === null ? [] : [
                        'id' => $objective->id ?? null,
                        'name' => $objective->code ?? null,
                        'label' => $objective->code ?? null,
                        'code' => $objective->code ?? null,
                        'description' => $objective->description ?? null,
                        'type_function_id' => $objective->type_of_function_id ?? null,
                        'success_indicators' => $objective->successIndicators->map(function ($option_si) {
                            return [
                                'id' => $option_si->id ?? null,
                                'name' => $option_si->code ?? null,
                                'label' => $option_si->code ?? null,
                                'code' => $option_si->code ?? null,
                                'description' => $option_si->description ?? null,
                                'objective_id' => $option_si->objective_id ?? null,
                            ];
                        })
                    ],
                    'success_indicator' => $success_indicator === null ? [] : [
                        'id' => $success_indicator->id ?? null,
                        'name' => $success_indicator->code ?? null,
                        'label' => $success_indicator->code ?? null,
                        'code' => $success_indicator->code ?? null,
                        'description' => $success_indicator->description ?? null,
                        'objective_id' => $success_indicator->objective_id ?? null,
                    ],
                    'other_objective' => $other_objective === null ? [] : [
                        'id' => $other_objective->id ?? null,
                        'description' => $other_objective->description ?? null,
                        'application_objective_id' => $other_objective->application_objective_id ?? null,
                    ],
                    'other_success_indicator' => $other_success_indicator === null ? [] : [
                        'id' => $other_success_indicator->id ?? null,
                        'description' => $other_success_indicator->description ?? null,
                        'application_objective_id' => $other_success_indicator->application_objective_id ?? null,
                    ],
                    'activity' => $appObj->activities === null ? [] : ActivityResource::collection($appObj->activities),
                ];
            }),
        ];
    }
}
