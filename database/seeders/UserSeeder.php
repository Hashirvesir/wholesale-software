<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@invenco.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Super Admin');

        // Create Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@invenco.com'],
            [
                'name' => 'Store Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('Manager');

        // Create Salesman User
        $salesman = User::firstOrCreate(
            ['email' => 'sales@invenco.com'],
            [
                'name' => 'Sales Representative',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $salesman->assignRole('Salesman');
    }
}
