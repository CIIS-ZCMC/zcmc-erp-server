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
            // 'activity_id' => 'required|integer',
            // 'object_category' => 'required|string',
            // "expense_class" => "required|string",
            // "purchase_type_id" => "required|integer",
            'PPMP_Items' => 'required|json',
            // 'PPMP_Items.*.ppmp_application_id' => 'required|integer',
            // 'PPMP_Items.*.item_id' => 'required|integer',
            // 'PPMP_Items.*.procurement_mode_id' => 'required|integer',
            // 'PPMP_Items.*.item_request_id' => 'required|integer',
            // 'PPMP_Items.*.total_quantity' => 'nullable|numeric',
            // 'PPMP_Items.*.estimated_budget' => 'nullable|numeric',
            // 'PPMP_Items.*.total_amount' => 'nullable|numeric',
            // 'PPMP_Items.*.remarks' => 'nullable|string',
            // 'PPMP_Items.*.comment' => 'nullable|string',
            // 'is_draft' => 'required|boolean',
        ];
    }
}
