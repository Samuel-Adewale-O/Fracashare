<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/']
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'OTP code is required',
            'otp.size' => 'OTP must be exactly 6 digits',
            'otp.regex' => 'OTP must contain only numbers'
        ];
    }
}