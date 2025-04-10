<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManageAopRequestResource extends JsonResource
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
            'aop_application_uuid' => $this->aop_application_uuid,
            'mission' => $this->mission,
            'status' => $this->status,
            'has_discussed' => $this->has_discussed,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'objectives' => $this->applicationObjectives->map(function ($objective) {
                // return [
                //     'id' => $objective->id,
                //     'description' => $objective->description,
                //     'objective_type' => $objective->function_objective ? $objective->function_objective->type_of_function_id : null,
                //     'success_indicator_id' => $objective->success_indicator_id,
                //     'success_indicator' => $objective->successIndicator ? [
                //         'id' => $objective->successIndicator->id,
                //         'description' => $objective->successIndicator->description,
                //         'code' => $objective->successIndicator->code,
                //     ] : null,
                //     'activities' => $objective->activities->map(function ($activity) {
                //         return [
                //             'id' => $activity->id,
                //             'activity_uuid' => $activity->activity_uuid,
                //             'activity_code' => $activity->activity_code,
                //             'name' => $activity->name,
                //             'is_gad_related' => $activity->is_gad_related,
                //             'cost' => $activity->cost,
                //             'start_month' => $activity->start_month,
                //             'end_month' => $activity->end_month,
                //         ];
                //     }),
                // ];
                return $objective;
            }),
        ];
    }
}
