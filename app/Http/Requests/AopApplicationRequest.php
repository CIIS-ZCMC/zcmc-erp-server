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
        $rules = [
            'status' => 'nullable|string|in:pending,draft',
        ];

        // If the status is not draft, enforce full validation
        if ($this->input('status') !== 'draft') {
            $rules = array_merge($rules, [
                'mission' => 'required|string',
                'has_discussed' => 'required|boolean',

                'remarks' => 'nullable|string',
                'application_objectives' => 'required|array',
                'application_objectives.*.objective_id' => 'required|exists:objectives,id',
                'application_objectives.*.success_indicator_id' => 'required|exists:success_indicators,id',
                'application_objectives.*.others_objective' => 'nullable|string',
                'application_objectives.*.other_success_indicator' => 'nullable|string',

                'application_objectives.*.activities' => 'required|array',
                'application_objectives.*.activities.*.name' => 'required|string',
                'application_objectives.*.activities.*.is_gad_related' => 'required|boolean',
                'application_objectives.*.activities.*.cost' => 'required|numeric|min:0',
                'application_objectives.*.activities.*.start_month' => 'required|date',
                'application_objectives.*.activities.*.end_month' => 'required|date|after_or_equal:application_objectives.*.activities.*.start_month',

                'application_objectives.*.activities.*.target' => 'nullable|array',
                'application_objectives.*.activities.*.target.first_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.second_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.third_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.fourth_quarter' => 'nullable|string',

                'application_objectives.*.activities.*.resources' => 'required|array',
                'application_objectives.*.activities.*.resources.*.item_id' => 'required|exists:items,id',
                'application_objectives.*.activities.*.resources.*.purchase_type_id' => 'required|exists:purchase_types,id',
                'application_objectives.*.activities.*.resources.*.quantity' => 'required|integer|min:1',
                'application_objectives.*.activities.*.resources.*.expense_class' => 'required|string',

                'application_objectives.*.activities.*.responsible_people' => 'required|array',
            ]);
        } else {
            // Relaxed validation for drafts: only validate if present
            $rules = array_merge($rules, [
                'mission' => 'sometimes|string',
                'has_discussed' => 'sometimes|boolean',
                'remarks' => 'nullable|string',
                'application_objectives' => 'sometimes|array',
                'application_objectives.*.objective_id' => 'sometimes|exists:objectives,id',
                'application_objectives.*.success_indicator_id' => 'sometimes|exists:success_indicators,id',
                'application_objectives.*.others_objective' => 'nullable|string',
                'application_objectives.*.other_success_indicator' => 'nullable|string',

                'application_objectives.*.activities' => 'sometimes|array',
                'application_objectives.*.activities.*.name' => 'sometimes|string',
                'application_objectives.*.activities.*.is_gad_related' => 'sometimes|boolean',
                'application_objectives.*.activities.*.cost' => 'sometimes|numeric|min:0',
                'application_objectives.*.activities.*.start_month' => 'sometimes|date',
                'application_objectives.*.activities.*.end_month' => 'sometimes|date',

                'application_objectives.*.activities.*.target' => 'nullable|array',
                'application_objectives.*.activities.*.target.first_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.second_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.third_quarter' => 'nullable|string',
                'application_objectives.*.activities.*.target.fourth_quarter' => 'nullable|string',

                'application_objectives.*.activities.*.resources' => 'sometimes|array',
                'application_objectives.*.activities.*.resources.*.item_id' => 'sometimes|exists:items,id',
                'application_objectives.*.activities.*.resources.*.purchase_type_id' => 'sometimes|exists:purchase_types,id',
                'application_objectives.*.activities.*.resources.*.quantity' => 'sometimes|integer|min:1',
                'application_objectives.*.activities.*.resources.*.expense_class' => 'sometimes|string',

                'application_objectives.*.activities.*.responsible_people' => 'sometimes|array',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mission.required' => 'The mission statement is required.',
            'mission.string' => 'The mission must be a text value.',
            'has_discussed.required' => 'Please indicate whether this has been discussed.',
            'has_discussed.boolean' => 'The has discussed field must be true or false.',

            'application_objectives.required' => 'At least one objective must be provided.',
            'application_objectives.array' => 'Objectives must be provided as a list.',
            'application_objectives.*.objective_id.required' => 'Each objective must have an ID.',
            'application_objectives.*.objective_id.exists' => 'The selected objective does not exist in our records.',
            'application_objectives.*.success_indicator_id.required' => 'Each objective must have a success indicator.',
            'application_objectives.*.success_indicator_id.exists' => 'The selected success indicator does not exist in our records.',

            'application_objectives.*.activities.required' => 'Each objective must have at least one activity.',
            'application_objectives.*.activities.array' => 'Activities must be provided as a list.',
            'application_objectives.*.activities.*.name.required' => 'Each activity must have a name.',
            'application_objectives.*.activities.*.is_gad_related.required' => 'Please specify if the activity is GAD related.',
            'application_objectives.*.activities.*.cost.required' => 'Each activity must have a cost.',
            'application_objectives.*.activities.*.cost.numeric' => 'The cost must be a number.',
            'application_objectives.*.activities.*.cost.min' => 'The cost cannot be negative.',
            'application_objectives.*.activities.*.start_month.required' => 'Each activity must have a start month.',
            'application_objectives.*.activities.*.start_month.date' => 'The start month must be a valid date.',
            'application_objectives.*.activities.*.end_month.required' => 'Each activity must have an end month.',
            'application_objectives.*.activities.*.end_month.date' => 'The end month must be a valid date.',
            'application_objectives.*.activities.*.end_month.after_or_equal' => 'The end month must be after or the same as the start month.',

            'application_objectives.*.activities.*.resources.required' => 'Each activity must have at least one resource.',
            'application_objectives.*.activities.*.resources.array' => 'Resources must be provided as a list.',
            'application_objectives.*.activities.*.resources.*.item_id.required' => 'Each resource must have an item ID.',
            'application_objectives.*.activities.*.resources.*.item_id.exists' => 'The selected item does not exist in our records.',
            'application_objectives.*.activities.*.resources.*.purchase_type_id.required' => 'Each resource must have a purchase type.',
            'application_objectives.*.activities.*.resources.*.purchase_type_id.exists' => 'The selected purchase type does not exist in our records.',
            'application_objectives.*.activities.*.resources.*.quantity.required' => 'Each resource must have a quantity.',
            'application_objectives.*.activities.*.resources.*.quantity.integer' => 'The quantity must be a whole number.',
            'application_objectives.*.activities.*.resources.*.quantity.min' => 'The quantity must be at least 1.',
            'application_objectives.*.activities.*.resources.*.expense_class.required' => 'Each resource must have an expense class.',

            'application_objectives.*.activities.*.responsible_people.required' => 'Each activity must have at least one responsible person.',
            'application_objectives.*.activities.*.responsible_people.array' => 'Responsible people must be provided as a list.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mission' => 'mission statement',
            'has_discussed' => 'discussion status',
            'application_objectives' => 'objectives',
            'application_objectives.*.objective_id' => 'objective',
            'application_objectives.*.success_indicator_id' => 'success indicator',
            'application_objectives.*.activities' => 'activities',
            'application_objectives.*.activities.*.name' => 'activity name',
            'application_objectives.*.activities.*.is_gad_related' => 'GAD related status',
            'application_objectives.*.activities.*.cost' => 'activity cost',
            'application_objectives.*.activities.*.start_month' => 'start month',
            'application_objectives.*.activities.*.end_month' => 'end month',
            'application_objectives.*.activities.*.resources' => 'resources',
            'application_objectives.*.activities.*.resources.*.item_id' => 'item',
            'application_objectives.*.activities.*.resources.*.purchase_type_id' => 'purchase type',
            'application_objectives.*.activities.*.resources.*.quantity' => 'quantity',
            'application_objectives.*.activities.*.resources.*.expense_class' => 'expense class',
            'application_objectives.*.activities.*.responsible_people' => 'responsible people',
        ];
    }
}
