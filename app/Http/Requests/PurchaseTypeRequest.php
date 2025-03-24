<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseTypeRequest extends FormRequest
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
            'purchase_types' => [
                'nullable',
                'array', // Ensure it's an array when bulk inserting
            ],
            'purchase_types.*.code' => 'required_with:purchase_types|string|max:255',
            'purchase_types.*.description' => 'nullable|string',
    
            'code' => 'required_without:purchase_types|string|max:255',
            'description' => 'nullable|string',        
        ];
    }
}
