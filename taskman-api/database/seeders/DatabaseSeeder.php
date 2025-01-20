<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // $this->call(PermissionsSeeder::class);

        // Seed roles and permissions first
        $this->call(PermissionsSeeder::class);

        // Create an admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Use a secure password
        ]);

        // Assign the 'admin' role to the admin user
        $adminUser->assignRole('admin');

        // Create an employee user
        $employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'), // Use a secure password
        ]);

        // Assign the 'employee' role to the employee user
        $employeeUser->assignRole('employee');

        // Create an employee instance linked to the employee user
        Employee::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'phone' => '1234567890', // Optional: Add a valid phone number
            'admin_id' => $adminUser->id, // Associate with the admin user
            'user_id' => $employeeUser->id,
        ]);
    }
}
