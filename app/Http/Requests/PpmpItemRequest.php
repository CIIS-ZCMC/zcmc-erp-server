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
            'ppmp_item' => 'required|array',
            'ppmp_item.*.ppmp_application_id' => 'required|integer',
            'ppmp_item.*.item_id' => 'required|integer',
            'ppmp_item.*.procurement_mode_id' => 'required|integer',
            'ppmp_item.*.item_request_id' => 'required|integer',
            'ppmp_item.*.total_quantity' => 'nullable|number',
            'ppmp_item.*.estimated_budget' => 'nullable|number',
            'ppmp_item.*.total_amount' => 'nullable|number',
            'ppmp_item.*.remarks' => 'nullable|string',
            'ppmp_item.*.comment' => 'nullable|string',
        ];
    }
}
