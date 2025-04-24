<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpmpItemRequest extends FormRequest
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
            'activity_id' => 'required|integer',
            'object_category' => 'required|string',
            "expense_class" => "required|string",
            "purchase_type_id" => "required|integer",
            'ppmp_item' => 'required|array',
            'ppmp_item.*.ppmp_application_id' => 'required|integer',
            'ppmp_item.*.item_id' => 'required|integer',
            'ppmp_item.*.procurement_mode_id' => 'required|integer',
            'ppmp_item.*.item_request_id' => 'required|integer',
            'ppmp_item.*.total_quantity' => 'nullable|numeric',
            'ppmp_item.*.estimated_budget' => 'nullable|numeric',
            'ppmp_item.*.total_amount' => 'nullable|numeric',
            'ppmp_item.*.remarks' => 'nullable|string',
            'ppmp_item.*.comment' => 'nullable|string',
            'is_draft' => 'required|boolean',
        ];
    }
}
