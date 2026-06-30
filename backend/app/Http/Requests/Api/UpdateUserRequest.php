<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuarioAutenticado = $this->user();
        $usuarioAlvo = $this->route('user');

        if (! $usuarioAutenticado || ! $usuarioAlvo instanceof User) {
            return false;
        }

        if ($usuarioAutenticado->can('user.update')) {
            return true;
        }

        return $usuarioAutenticado->hasRole('Atendente')
            && $usuarioAutenticado->is($usuarioAlvo);
    }

    public function rules(): array
    {
        $usuario = $this->route('user');
        $usuarioAutenticado = $this->user();

        $regrasRole = [
            'required',
            'integer',
            Rule::exists('roles', 'id')->where('guard_name', 'web'),
        ];

        if (
            $usuario instanceof User
            && $usuarioAutenticado?->hasRole('Atendente')
            && $usuarioAutenticado->is($usuario)
        ) {
            $roleIdAtual = $usuario->roles()->value('id');

            if ($roleIdAtual !== null) {
                $regrasRole[] = Rule::in([$roleIdAtual]);
            }
        }

        return [
            'name' => ['required', 'string'],
            'role_id' => $regrasRole,
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
        ];
    }
}
