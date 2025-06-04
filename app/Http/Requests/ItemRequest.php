<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'items' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.code' => 'required_with:items|string|max:255',
            'items.*.estimated_budget' => 'required|numeric',
            'items.*.item_unit_id' => 'required|int',
            'items.*.terminology_category_id' => 'required|int',
            'items.*.item_category_id' => 'required|int',
            'items.*.item_classification_id' => 'nullable|int',
            'items.*.specifications' => [
                'nullable',
                'array'
            ],
            'items.*.specifications.description' => 'nullable|string',
    
            'name' => 'required_without:items|string|max:255',
            'code' => 'required_without:items|string|max:255',
            'estimated_budget' => 'required|numeric',
            'item_unit_id' => 'required|int',   
            'terminology_category_id' => 'required|int',   
            'item_category_id' => 'required|int',     
            'item_classification_id' => 'nullable|int',
            'specifications' => [
                'nullable',
                'array'
            ],
            'specifications.description' => 'nullable|string'     
        ];
    }
}
