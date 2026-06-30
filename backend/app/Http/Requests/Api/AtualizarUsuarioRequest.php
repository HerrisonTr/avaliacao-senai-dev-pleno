<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.update') ?? false;
    }

    public function rules(): array
    {
        $usuario = $this->route('user');

        return [
            'name' => ['required', 'string'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('guard_name', 'web'),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
        ];
    }
}
