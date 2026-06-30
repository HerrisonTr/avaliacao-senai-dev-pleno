<?php

namespace App\Http\Requests\Api\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
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
        return [
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase(),
            ],
        ];
    }
}
