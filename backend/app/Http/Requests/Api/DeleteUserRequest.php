<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuarioAutenticado = $this->user();
        /** @var User|null $usuarioDaRota */
        $usuarioDaRota = $this->route('user');

        // Não permite excluir o próprio usuário
        if (! $usuarioAutenticado || ! $usuarioAutenticado->can('user.delete')) {
            return false;
        }

        if ($usuarioDaRota && $usuarioAutenticado->is($usuarioDaRota)) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
