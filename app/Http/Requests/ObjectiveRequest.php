<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ObjectiveRequest extends FormRequest
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
            'objectives' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'objectives.*.code' => 'required_with:objectives|string|max:255',
            'objectives.*.description' => 'nullable|string',
    
            'code' => 'required_without:objectives|string|max:255',
            'description' => 'nullable|string',     
        ];
    }
}
