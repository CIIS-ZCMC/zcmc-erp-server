<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypeOfFunctionRequest extends FormRequest
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
            'type_of_functions' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'type_of_functions.*.type' => 'required_with:type_of_functions|string|max:255',
    
            'type' => 'required_without:type_of_functions|string|max:255'
        ];
    }
}
