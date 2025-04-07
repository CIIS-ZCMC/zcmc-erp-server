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
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|integer',
            'ppmp_application_id' => 'required|integer',
            'procurement_mode_id' => 'required|integer',
            'item_request_id' => 'required|integer',
        ];
    }
}
