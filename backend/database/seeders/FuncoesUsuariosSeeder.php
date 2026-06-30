<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FuncoesUsuarios extends Seeder
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
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $papelAdministrador = Role::findOrCreate('Administrador', 'web');
        $papelAtendente = Role::findOrCreate('Atendente', 'web');

        $papelAdministrador->syncPermissions($this->permissoesAdministrador);
        $papelAtendente->syncPermissions(['user.list']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
