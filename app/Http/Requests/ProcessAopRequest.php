<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessAopRequest extends FormRequest
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
     *F
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'aop_application_id' => 'required|integer',
            'status' => 'required|string',
            'remarks' => 'nullable|string|max:500',
            'authorization_pin' => 'required|integer|digits:6',
        ];
    }
}
