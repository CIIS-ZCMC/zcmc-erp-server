<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemClassificationRequest extends FormRequest
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
            'item_classifications' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'item_classifications.*.name' => 'required_with:item_classifications|string|max:255',
            'item_classifications.*.code' => 'required_with:item_classifications|string|max:255',
            'item_classifications.*.description' => 'nullable|string',
    
            'name' => 'required_without:item_classifications|string|max:255',
            'code' => 'required_without:item_classifications|string|max:255',
            'description' => 'nullable|string',  
        ];
    }
}
