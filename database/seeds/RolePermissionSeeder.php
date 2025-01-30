<?php

namespace Shahnewaz\PermissibleNg\Database\Seeder;

use Shahnewaz\PermissibleNg\Role;
use Illuminate\Database\Seeder;
use Shahnewaz\PermissibleNg\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Role::truncate();

        // Create major roles
        Role::firstOrcreate(['name' => 'Super User'], ['code' => 'su', 'weight' => 0]);
        Role::firstOrcreate(['name' => 'Admin'], ['code' => 'admin', 'weight' => 1]);
        Role::firstOrcreate(['name' => 'User'], ['code' => 'user', 'weight' => 999]);

        // Create permissions
        Permission::truncate();
        $permissions = [
            "users.list" => [
                'Super User',
                'Admin',
                'Staff'
            ],
            "users.create" => [
                'Super User',
            ],
            "users.edit" => [
                'Super User',
            ],
            "users.delete" => [
                'Super User',
            ],
            "roles.list" => [
                'Super User'
            ],
            "roles.create" => [
                'Super User'
            ],
            "roles.edit" => [
                'Super User'
            ],
            "roles.delete" => [
                'Super User'
            ],
        ];

        foreach ($permissions as $permission => $roleName) {
            $permissionObject = Permission::createPermission($permission);
            $rolesIds = Role::whereIn('name', $roleName)->pluck('id')->toArray();
            $permissionObject->roles()->sync($rolesIds);
        }

        if (config('permissible.first_last_name_migration', false) === true) {
            $fillables = [
                'first_name' => 'Super',
                'last_name' => 'User',
                'password' => 'super_user'
            ];
        } else {
            $fillables = [
                'password' => 'super_user'
            ];
        }

        $su = \App\Models\User::firstOrCreate(
            ['email' => 'super_user@app.dev'],
            $fillables
        );

        $su->roles()->sync([1]);
    }
}
