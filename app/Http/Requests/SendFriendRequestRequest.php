<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendFriendRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'friend_id' => 'required|integer|exists:users,id|different:user_id',
        ];
    }

    public function messages()
    {
        return [
            'friend_id.required' => 'Friend ID is required',
            'friend_id.exists' => 'User does not exist',
            'friend_id.different' => 'Cannot send friend request to yourself',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->user()->id,
        ]);
    }
}
