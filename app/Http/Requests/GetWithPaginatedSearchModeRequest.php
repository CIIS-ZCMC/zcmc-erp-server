<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetWithPaginatedSearchModeRequest extends FormRequest
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
            'id' => 'nullable|integer',
            'search' => [
                'nullable',
                'string',
            ],
            'mode' => 'nullable|string',
            'per_page' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    // Required only if search exists without mode
                    return !request()->has('id') && 
                           !request()->has('mode');
                }),
                'min:1',
                'max:100'
            ],
            'page' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    // Required only if per_page exists
                    return request()->has('per_page');
                }),
                'min:1'
            ],
        ];
    }
}
