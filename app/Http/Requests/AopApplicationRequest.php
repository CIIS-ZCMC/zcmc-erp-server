<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AopApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'division_chief_id' => 'required|exists:users,id',
            'mcc_chief_id' => 'required|exists:users,id',
            'planning_officer_id' => 'required|exists:users,id',
            'mission' => 'required|string',
            'status' => 'required|string',
            'has_discussed' => 'required|boolean',
            'remarks' => 'nullable|string',

            'application_objectives' => 'required|array',
            'application_objectives.*.objective_id' => 'required|exists:objectives,id',
            'application_objectives.*.success_indicator_id' => 'required|exists:success_indicators,id',
            'application_objectives.*.others_objective' => 'nullable|string',
            'application_objectives.*.other_success_indicator' => 'nullable|string',

            'application_objectives.*.activities' => 'required|array',

            'application_objectives.*.activities.*.activity_code' => 'required|string',
            'application_objectives.*.activities.*.name' => 'required|string',
            'application_objectives.*.activities.*.is_gad_related' => 'required|boolean',
            'application_objectives.*.activities.*.cost' => 'required|numeric|min:0',
            'application_objectives.*.activities.*.start_month' => 'required|date',
            'application_objectives.*.activities.*.end_month' => 'required|date|after_or_equal:application_objectives.*.activities.*.start_month',

            'application_objectives.*.activities.*.target' => 'required|array',
            'application_objectives.*.activities.*.target.first_quarter' => 'required|string',
            'application_objectives.*.activities.*.target.second_quarter' => 'required|string',
            'application_objectives.*.activities.*.target.third_quarter' => 'required|string',
            'application_objectives.*.activities.*.target.fourth_quarter' => 'required|string',

            'application_objectives.*.activities.*.resources' => 'required|array',
            'application_objectives.*.activities.*.resources.*.item_id' => 'required|exists:items,id',
            'application_objectives.*.activities.*.resources.*.purchase_type_id' => 'required|exists:purchase_types,id',
            'application_objectives.*.activities.*.resources.*.object_category' => 'required|string',
            'application_objectives.*.activities.*.resources.*.quantity' => 'required|integer|min:1',
            'application_objectives.*.activities.*.resources.*.expense_class' => 'required|string',

            'application_objectives.*.activities.*.responsible_people' => 'required|array',
            'application_objectives.*.activities.*.responsible_people.*.user_id' => 'nullable|exists:users,id',
            'application_objectives.*.activities.*.responsible_people.*.division_id' => 'nullable|exists:divisions,id',
            'application_objectives.*.activities.*.responsible_people.*.department_id' => 'nullable|exists:departments,id',
            'application_objectives.*.activities.*.responsible_people.*.section_id' => 'nullable|exists:sections,id',
            'application_objectives.*.activities.*.responsible_people.*.unit_id' => 'nullable|exists:units,id',
            'application_objectives.*.activities.*.responsible_people.*.designation_id' => 'required|exists:designations,id',
        ];
    }
}
