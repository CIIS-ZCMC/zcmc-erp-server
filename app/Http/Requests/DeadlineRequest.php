<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeadlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Update this if you have authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            // Validation rules for AOP
            'aop_deadline' => 'nullable|date',
            'aop_start_date' => 'nullable|date|after_or_equal:aop_deadline', // Ensure start date is after or equal to deadline

            // Validation rules for PPMP
            'ppmp_deadline' => 'nullable|date',
            'ppmp_start_date' => 'nullable|date|after_or_equal:ppmp_deadline', // Ensure start date is after or equal to deadline
        ];
    }

    /**
     * Get custom attributes for validation errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'aop_deadline' => 'AOP deadline',
            'aop_start_date' => 'AOP start date',
            'ppmp_deadline' => 'PPMP deadline',
            'ppmp_start_date' => 'PPMP start date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'aop_deadline.date' => 'The AOP deadline must be a valid date.',
            'aop_start_date.after_or_equal' => 'The AOP start date must be after or equal to the AOP deadline.',
            'ppmp_deadline.date' => 'The PPMP deadline must be a valid date.',
            'ppmp_start_date.after_or_equal' => 'The PPMP start date must be after or equal to the PPMP deadline.',
        ];
    }
}
