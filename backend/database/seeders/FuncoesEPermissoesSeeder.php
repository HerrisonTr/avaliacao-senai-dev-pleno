<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FuncoesEPermissoesSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $permissoes = [
        'user.list',
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissoes as $permissao) {
            Permission::findOrCreate($permissao, 'web');
        }

        $papelAdministrador = Role::findOrCreate('Administrador', 'web');
        $papelAtendente = Role::findOrCreate('Atendente', 'web');

        $papelAdministrador->syncPermissions($this->permissoes);
        $papelAtendente->syncPermissions(['user.list']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
