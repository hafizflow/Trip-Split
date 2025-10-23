<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripMemberRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'role' => 'required|string|in:member,admin',
        ];
    }

    public function messages()
    {
        return [
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either member or admin',
        ];
    }
}
