<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $this->call([
            RolesAndPermissionSeeder::class,
            AssetStatusSeeder::class,
            CategorySeeder::class,
            LocationSeeder::class,

        ]);

        //Create Admin User
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $adminUser->assignRole('admin');


        // 2. We create users, but we DON'T assign roles yet.
        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);

        User::factory(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        //User::factory(10)->create();


        Asset::factory(50)->create();
    }
}
