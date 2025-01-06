<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;


class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        app()[
            \Spatie\Permission\PermissionRegistrar::class
        ]->forgetCachedPermissions();

        $arrayOfPermissionNames = [
            "access tasks",
            "create tasks",
            "update tasks",
            "delete tasks",
            "access employees",
            "create employee",
            "update employee",
            "delete employee"
        ];
        $permissions = collect($arrayOfPermissionNames)->map(function (
            $permission
            ){
                return ["name"=>$permission, "guard_name"=>"api"];
        });
        Permission::insert($permissions->toArray());

        // Role::create(["name"=>"admin"])->givePermissionTo(Permission::all());
        // Role::create(["name"=>"employee"])->givePermissionTo(["access tasks", "update tasks"]);

        // Create roles with the "api" guard
        $adminRole = Role::create(["name" => "admin", "guard_name" => "api"]);
        $employeeRole = Role::create(["name" => "employee", "guard_name" => "api"]);

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $employeeRole->givePermissionTo(["access tasks", "update tasks", "update employee"]);

        User::find(7)->assignRole('admin');
        User::find(9)->assignRole('employee');
    }
}
