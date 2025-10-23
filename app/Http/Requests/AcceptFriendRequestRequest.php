<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptFriendRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'request_id' => 'required|integer|exists:friend_requests,id',
        ];
    }

    public function messages()
    {
        return [
            'request_id.required' => 'Friend request ID is required',
            'request_id.exists' => 'Friend request does not exist',
        ];
    }
}
