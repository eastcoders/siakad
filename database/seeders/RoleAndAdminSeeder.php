<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the admin role if it doesn't already exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Optional: you can create other roles here
        // $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create an admin user or update existing
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Assign exactly the admin role
        $admin->assignRole($adminRole);
    }
}
