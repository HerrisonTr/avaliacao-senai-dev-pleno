<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioAtendenteSeeder extends Seeder
{
    public function run(): void
    {
        $usuarioAtendente = User::query()->updateOrCreate(
            ['email' => 'atendente@atendente.com'],
            [
                'name' => 'Atendente',
                'password' => '123qwe!!',
            ],
        );

        $usuarioAtendente->syncRoles(['Atendente']);
    }
}
