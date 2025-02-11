<?php

namespace Shahnewaz\PermissibleNg\Database\Seeder;

use Shahnewaz\PermissibleNg\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        // Use transactions for atomicity
        DB::beginTransaction();

        try {
            // Create major roles without weight
            $roles = [
                ['name' => 'Super User', 'code' => 'su'],
                ['name' => 'Admin', 'code' => 'admin'],
                ['name' => 'User', 'code' => 'user'],
            ];

            foreach ($roles as $role) {
                Role::firstOrCreate(
                    ['code' => $role['code']], 
                    $role
                );
            }

            // Define permissions with their roles
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

            // Create permissions and assign to roles without truncating
            foreach ($permissions as $permissionName => $roleNames) {
                $permission = Permission::firstOrCreate(
                    Permission::getPermissionParts($permissionName)
                );

                // Get role IDs, filtering out non-existent roles
                $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();
                
                // Sync without detaching to preserve existing relationships
                $permission->roles()->syncWithoutDetaching($roleIds);
            }

            // Create super user if doesn't exist
            $userFields = [
                'email' => 'super_user@app.dev',
                'password' => Hash::make('super_user'), // Hash password
            ];

            if (config('permissible.first_last_name_migration', false)) {
                $userFields['first_name'] = 'Super';
                $userFields['last_name'] = 'User';
            } else {
                $userFields['name'] = 'Super User';
            }

            $su = \App\Models\User::firstOrCreate(
                ['email' => $userFields['email']],
                $userFields
            );

            // Attach super user role without detaching other roles
            $suRole = Role::where('code', 'su')->first();
            if ($suRole) {
                $su->roles()->syncWithoutDetaching([$suRole->id]);
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
