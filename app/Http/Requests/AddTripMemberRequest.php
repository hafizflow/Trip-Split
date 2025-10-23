<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTripMemberRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'sometimes|string|in:member,admin',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User does not exist',
            'role.in' => 'Role must be either member or admin',
        ];
    }
}
