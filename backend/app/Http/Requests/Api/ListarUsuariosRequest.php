<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ListarUsuariosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.list') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
