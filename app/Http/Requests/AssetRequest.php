<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', Rule::in(['real_estate', 'stocks'])],
            'total_value' => ['required', 'numeric', 'min:0'],
            'minimum_investment' => ['required', 'numeric', 'min:0'],
            'total_shares' => ['required', 'integer', 'min:1'],
            'share_price' => ['required', 'numeric', 'min:0'],
            'expected_roi' => ['required', 'numeric', 'min:0'],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'images.*' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'metadata' => ['sometimes', 'array'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return array_merge(['sometimes'], is_array($rule) ? $rule : [$rule]);
            }, $rules);
        }

        return $rules;
    }
}