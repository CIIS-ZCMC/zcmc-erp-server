<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetObjectiveSuccessIndicatorRequest extends FormRequest
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
            'search' => 'nullable|string',
            'mode' => 'nullable|string',
            'per_page' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return !request()->has('mode') && request()->has('search');
                }),
            ],
            'page' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return !request()->has('mode') && request()->has('search');
                }),
            ],
        ];
    }
}
