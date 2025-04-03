<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequestRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'variant' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric',
            'item_unit_id' => 'required|int',     
            'item_category_id' => 'required|int',     
            'item_classification_id' => 'required|int',    
            'reason' => 'nullable|string', 
        ];
    }
}
