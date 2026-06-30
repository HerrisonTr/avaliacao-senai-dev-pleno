<?php

namespace App\Http\Requests\Api\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('guard_name', 'web'),
            ],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase(),
            ],
        ];
    }
}
