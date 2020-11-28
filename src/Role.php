<?php 
namespace Shahnewaz\PermissibleNg;

use App\User;
use Shahnewaz\PermissibleNg\Permission;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'code', 'weight'];

    /**
     * Timestamps flag (false will disable timestamps)
     *
     * @var boolean
     */
    public $timestamps = false;

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
    public function hasPermission ($permission) {
        $permission = explode('.', $permission, 2);
        
        return !$this->permissions->filter(function($item) use($permission) {
            if($item->type == $permission[0] && $item->name == '*') { return true; }
            if (!isset($permission[1])) {
                return false;
            }
            if($item->type == $permission[0] && $item->name == $permission[1]) { return true; }
            return false;
        })->isEmpty();
    }
}
