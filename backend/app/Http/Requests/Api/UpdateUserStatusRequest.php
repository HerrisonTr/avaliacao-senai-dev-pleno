<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuarioAutenticado = $this->user();

        /** @var User|null $usuarioDaRota */
        $usuarioDaRota = $this->route('user');

        // Não permite desabilitar o próprio usuário
        if (! $usuarioAutenticado || ! $usuarioAutenticado->can('user.update')) {
            return false;
        }

        if ($usuarioDaRota && $usuarioAutenticado->is($usuarioDaRota)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
        ];
    }
}
