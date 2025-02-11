<?php 
namespace Shahnewaz\PermissibleNg;

use App\Models\User;
use Shahnewaz\PermissibleNg\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role
 * 
 * @package Shahnewaz\PermissibleNg
 * @property int $id
 * @property string $name
 * @property string $code
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|Permission[] $permissions
 */
class Role extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'code'];

    /**
     * Timestamps flag (false will disable timestamps)
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get cached role by code or name
     * 
     * @param string $identifier
     * @return Role|null
     */
    public static function getCachedRole($identifier)
    {
        return cache()->remember(
            "role.{$identifier}", 
            3600, 
            fn() => static::where('code', $identifier)
                         ->orWhere('name', $identifier)
                         ->first()
        );
    }

    /**
     * Users that belongs to this Role
     * */
    public function users () {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Permissions belonging to this Role
     * */
    public function permissions () {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Check if this Role has particular permission
     * */
    public function hasPermission($permission): bool {
        $permission = explode('.', $permission, 2);
        
        // Cache permissions to avoid repeated database queries
        return $this->permissions->contains(function($item) use($permission) {
            if ($item->type === $permission[0] && $item->name === '*') {
                return true;
            }
            return isset($permission[1]) && 
                   $item->type === $permission[0] && 
                   $item->name === $permission[1];
        });
    }

    /**
     * Bulk check multiple permissions
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasPermissions(array $permissions): bool
    {
        return collect($permissions)->every(fn($permission) => $this->hasPermission($permission));
    }
}
