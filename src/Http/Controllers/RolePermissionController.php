<?php

namespace Shahnewaz\Permissible\Http\Controllers;

use Illuminate\Http\Request;
use Shahnewaz\Permissible\Role;
use Shahnewaz\Permissible\Permission;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class RolePermissionController extends Controller
{

    public function __construct () {
        $this->ifConfigured();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        $roles = Role::all();
        return view('permissible::acl.role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function getNewRole()
    {
        $role = new Role;
        return view('permissible::acl.role.form', compact('role'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postRole(Role $role, Request $request)
    {
        $role = $request->get('id') ? Role::findOrFail($request->get('id')) : new Role;
        
        $rules = [
            'role_name' => 'required|max:25|unique:roles,name,'.$role->id,
            'code' => 'required|alpha-dash|unique:roles,code,'.$role->id,
            'weight' => 'required|integer|max:10'
        ];
        $request->validate($rules);

        $role->name = $request->get('role_name');
        $role->code = $request->get('code');
        $role->weight = $request->get('weight');
        $role->save();
        
        return redirect()->back()->withSuccess(trans('permissible::core.saved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setRolePermissions(Role $role){

        $permissions = Permission::all();
        $rolePermissionNameLists = [];
        
        if($role->permissions->count() != 0){
            $rolePermissions =  $role->permissions;
            foreach($rolePermissions as $rolePermission){
                $rolePermissionNameLists[] =  ucwords($rolePermission->type).' '.ucwords($rolePermission->name);
            }
        }

       return view('permissible::acl.role-permissions.form', compact('role', 'permissions', 'rolePermissionNameLists'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function postRolePermissions(Request $request)
    {
        $role = Role::find($request->get('role_id'));

        $permissions = Permission::all();
        $newPermissions = [];
        foreach ($permissions as $permission) {
            if(!empty($request->get('permissions'.$permission->id))){
                $newPermissions[] = $permission->id; 
            }
            $role->permissions()->sync($newPermissions);
        }
        return redirect()->route('permissible.role.index')->withSuccess(trans('permissible::core.saved'));
    }

    public function ifConfigured () {
        $configured = config('permissible.first_last_name_migration', false) === true && config('permissible.enable_routes', false) === true;
        if (!$configured) {
            app()->abort(403);
        }
    }
    
}
