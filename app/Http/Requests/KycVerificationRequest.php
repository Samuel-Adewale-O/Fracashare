<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bvn' => ['required_without:nin', 'string', 'size:11'],
            'nin' => ['required_without:bvn', 'string', 'size:11'],
        ];
    }

    public function messages(): array
    {
        return [
            'bvn.required_without' => 'Either BVN or NIN is required for verification',
            'nin.required_without' => 'Either BVN or NIN is required for verification',
            'bvn.size' => 'BVN must be exactly 11 digits',
            'nin.size' => 'NIN must be exactly 11 digits'
        ];
    }
}