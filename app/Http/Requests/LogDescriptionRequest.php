<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogDescriptionRequest extends FormRequest
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
            'log_descriptions' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'log_descriptions.*.title' => 'required_with:log_descriptions|string|max:255',
            'log_descriptions.*.code' => 'required_with:log_descriptions|string|max:255',
            'log_descriptions.*.description' => 'nullable|string',
    
            'title' => 'required_without:log_descriptions|string|max:255',
            'code' => 'required_without:log_descriptions|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
