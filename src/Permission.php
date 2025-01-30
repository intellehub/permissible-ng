<?php 
namespace Shahnewaz\PermissibleNg;

use Shahnewaz\PermissibleNg\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Permission
 * 
 * @package Shahnewaz\PermissibleNg
 * @property int $id
 * @property string $type
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|Role[] $roles
 */
class Permission extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = ['type', 'name'];

    // Add unique constraint to prevent duplicates
    protected $unique = ['type', 'name'];

    /**
     * @param $permission
     * @return array
     */
    public static function getPermissionParts($permission): array {
        $parts = explode('.', $permission, 2);
        return [
            'type' => $parts[0] ?? null,
            'name' => $parts[1] ?? '*'  // Default to wildcard for inheritance
        ];
    }

    public function roles () {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Creates a Permission passed in the form `type.name`
     *
     * @param $permission
     * @return Shahnewaz\PermissibleNg\Permission
     */
    public static function createPermission($permission) {
        $params = self::getPermissionParts($permission);
        return static::updateOrCreate($params);
    }

    /**
     * Finds a Permission passed in the form `type.name`
     *
     * @param $permission
     * @return Shahnewaz\PermissibleNg\Permission | null
     */
    public static function getPermission($permission) {
        $params = self::getPermissionParts($permission);
        return static::where($params)->first();
    }

    /**
     * Check if this permission inherits from a wildcard permission
     * 
     * @param string $type
     * @return bool
     */
    public function inheritsFrom($type): bool
    {
        return $this->type === $type && $this->name === '*';
    }
}
