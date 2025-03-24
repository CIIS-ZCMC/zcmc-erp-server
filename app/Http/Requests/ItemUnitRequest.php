<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemUnitRequest extends FormRequest
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
            'item_units' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'item_units.*.name' => 'required_with:item_units|string|max:255',
            'item_units.*.code' => 'required_with:item_units|string|max:255',
            'item_units.*.description' => 'nullable|string',
    
            'name' => 'required_without:item_units|string|max:255',
            'code' => 'required_without:item_units|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
