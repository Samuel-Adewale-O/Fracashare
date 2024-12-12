<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'asset_id' => ['required', 'exists:assets,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', Rule::in(['sell_asset', 'upgrade_asset', 'financial_decision', 'other'])],
            'voting_starts_at' => ['required', 'date', 'after:now'],
            'voting_ends_at' => ['required', 'date', 'after:voting_starts_at'],
            'metadata' => ['sometimes', 'array']
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return array_merge(['sometimes'], is_array($rule) ? $rule : [$rule]);
            }, $rules);
        }

        return $rules;
    }
}