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
            "access users",
        ];
        $permissions = collect($arrayOfPermissionNames)->map(function (
            $permission
            ){
                return ["name"=>$permission, "guard_name"=>"web"];
        });
        Permission::insert($permissions->toArray());

        Role::create(["name"=>"admin"])->givePermissionTo(Permission::all());
        Role::create(["name"=>"employee"])->givePermissionTo(["access tasks", "update tasks"]);

        User::find(7)->assignRole("admin");
        User::find(9)->assignRole("employee");
    }
}
