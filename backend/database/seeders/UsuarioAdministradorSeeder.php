<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $usuarioAdministrador = User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => '123qwe!!',
            ],
        );

        $usuarioAdministrador->syncRoles(['Administrador']);
    }
}
