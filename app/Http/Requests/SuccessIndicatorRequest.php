<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuccessIndicatorRequest extends FormRequest
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
            'success_indicators' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'success_indicators.*.code' => 'required_with:success_indicators|string|max:255',
            'success_indicators.*.description' => 'nullable|string',
    
            'code' => 'required_without:success_indicators|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
