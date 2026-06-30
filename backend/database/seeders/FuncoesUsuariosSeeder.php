<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FuncoesUsuariosSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $permissoesAdministrador = [
        'user.list',
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
        'attendant-availability.list',
        'attendant-availability.view',
        'attendant-availability.create',
        'attendant-availability.update',
        'attendant-availability.delete',
        'service.list',
        'appointment.list',
        'appointment.view',
        'appointment.create',
        'appointment.update',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $papelAdministrador = Role::findOrCreate('Administrador', 'web');
        $papelAtendente = Role::findOrCreate('Atendente', 'web');

        $papelAdministrador->syncPermissions($this->permissoesAdministrador);
        $papelAtendente->syncPermissions([
            'user.list',
            'attendant-availability.list',
            'attendant-availability.view',
            'service.list',
            'appointment.list',
            'appointment.view',
            'appointment.create',
            'appointment.update',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
