<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpmpApplicationRequest extends FormRequest
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
            'aop_application_id' => 'required|array',
            'user_id' => 'required|integer',
            'division_chief_id' => 'required|integer',
            'budget_officer_id' => 'required|integer',
            'ppmp_total' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ];
    }
}
