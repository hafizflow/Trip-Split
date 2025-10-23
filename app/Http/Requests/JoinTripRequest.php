<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinTripRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|size:6|exists:trips,code',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Trip code is required',
            'code.size' => 'Trip code must be exactly 6 characters',
            'code.exists' => 'Invalid trip code',
        ];
    }
}
