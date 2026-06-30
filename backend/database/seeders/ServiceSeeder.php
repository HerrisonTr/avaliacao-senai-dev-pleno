<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Atendimento básico', 'price' => 50.00],
            ['name' => 'Atendimento padrão', 'price' => 100.00],
            ['name' => 'Atendimento premium', 'price' => 150.00],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['name' => $service['name']],
                ['price' => $service['price'], 'active' => true],
            );
        }
    }
}
