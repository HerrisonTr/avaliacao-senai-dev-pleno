<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissoesSeeder extends Seeder
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

        foreach ($this->permissoes as $permissao) {
            Permission::findOrCreate($permissao, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
