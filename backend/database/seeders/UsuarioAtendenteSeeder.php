<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioAtendenteSeeder extends Seeder
{
    public function run(): void
    {
        $atendentes = [
            [
                'email' => 'atendente@atendente.com',
                'name' => 'Atendente',
            ],
            [
                'email' => 'ana.souza@atendente.com',
                'name' => 'Ana Souza',
            ],
            [
                'email' => 'bruno.costa@atendente.com',
                'name' => 'Bruno Costa',
            ],
            [
                'email' => 'camila.oliveira@atendente.com',
                'name' => 'Camila Oliveira',
            ],
            [
                'email' => 'diego.almeida@atendente.com',
                'name' => 'Diego Almeida',
            ],
            [
                'email' => 'fernanda.lima@atendente.com',
                'name' => 'Fernanda Lima',
            ],
        ];

        foreach ($atendentes as $atendente) {
            $usuarioAtendente = User::query()->updateOrCreate(
                ['email' => $atendente['email']],
                [
                    'name' => $atendente['name'],
                    'password' => '123qwe!!',
                ],
            );

            $usuarioAtendente->syncRoles(['Atendente']);
        }
    }
}
