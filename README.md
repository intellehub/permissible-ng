# Permissible NG

A flexible and powerful Laravel package for managing roles and permissions with hierarchical support.

## Features

- Role-based access control (RBAC)
- Hierarchical roles with weight-based inheritance
- Permission management
- Route middleware for roles and permissions
- Caching support for optimal performance
- Soft delete support

## Installation

```bash
composer require shah-newaz/permissible-ng
```

```bash
php artisan vendor:publish --provider="Shahnewaz\PermissibleNg\Providers\PermissibleServiceProvider"
```

Run the migrations:

```bash
php artisan migrate
```

## Usage

### Setup User Model

Add the `Permissible` trait to your User model:

```php
use Shahnewaz\PermissibleNg\Traits\Permissible;

class User extends Authenticatable
{
    use Permissible;
    
    // ... rest of your User model
}
```

### Managing Roles and Permissions

```php
// Create a role
$role = Role::create([
    'name' => 'Admin',
    'code' => 'admin',
    'weight' => 1
]);

// Create a permission
$permission = Permission::createPermission('users.create');

// Assign permission to role
$role->permissions()->attach($permission);

// Assign role to user
$user->roles()->attach($role);
```

### Checking Permissions

```php
// Check single permission
$user->hasPermission('users.create');

// Check multiple permissions (requires all)
$user->hasPermissions(['users.create', 'users.delete']);

// Check permission type
$user->hasPermissionType('users');
```

### Route Protection

Use the elegant route middleware syntax:

```php
// Protect routes with roles
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->roles(['admin', 'super-admin']);

// Protect routes with permissions
Route::get('/users', [UserController::class, 'index'])
    ->permissions(['users.view']);

// Combine both
Route::get('/users/create', [UserController::class, 'create'])
    ->roles(['admin'])
    ->permissions(['users.create']);
```

### Role Hierarchy

Roles have weights - lower weight means higher privilege. For example:

```php
// Super Admin (highest privilege)
Role::create(['name' => 'Super Admin', 'code' => 'su', 'weight' => 0]);

// Admin (medium privilege)
Role::create(['name' => 'Admin', 'code' => 'admin', 'weight' => 1]);

// User (lowest privilege)
Role::create(['name' => 'User', 'code' => 'user', 'weight' => 10]);
```

If a route is permitted for a role with weight 10, any role with a lower weight will automatically have access to that route.

### Permission Wildcards

Use wildcards for broader permission control:

```php
// Grant all user permissions
Permission::createPermission('users.*');

// Check if user has any user permission
$user->hasPermissionType('users');
```

## Configuration

The package configuration can be modified in `config/permissible.php`:

```php
return [
    'enable_routes' => true,
    'enable_user_management_routes' => true,
    'first_last_name_migration' => true,
    'default_fallback_route' => 'backend.dashboard',
    'hierarchy' => true,
];
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

