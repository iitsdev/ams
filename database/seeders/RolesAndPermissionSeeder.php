<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;


class RolesAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //Define Permissions
        $permissions = [
            'view assets',
            'create assets',
            'edit assets',
            'delete assets',
            'assign assets',
            'run reports',
            'manage users',
            'mnanage settings',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'view suppliers',
            'create departments',
            'edit departments',
            'delete departments',
            'view departments',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        // Role: User (can only view assigned assets)
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo(['view assets']);

        // Role: Manager (can manage assets and run reports)
        $manageRole = Role::create(['name' => 'manager']);
        $manageRole->givePermissionTo([
            'view assets',
            'create assets',
            'edit assets',
            'delete assets',
            'assign assets',
            'run reports',
        ]);

        // Role: Admin (has all permissions)
        $adminRole = Role::create(['name' => 'admin']);
        // The admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());
    }
}
