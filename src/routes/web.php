<?php

Route::namespace('Shahnewaz\Permissible\Http\Controllers')->middleware('web')->group(function () {
	
	Route::middleware(['auth', 'role:su'])->prefix('permissible')->group(function () {
    // User Manager
    Route::get('/users', 'UsersController@index')->name('permissible.user.index');
    Route::get('/users/new', 'UsersController@form')->name('permissible.user.new');
    Route::get('/users/{user}', 'UsersController@form')->name('permissible.user.form');
    Route::post('/users/save', 'UsersController@post')->name('permissible.user.save');
    Route::post('/users/{user}/delete', 'UsersController@delete')->name('permissible.user.delete');
    Route::post('/users/{user}/restore', 'UsersController@restore')->name('permissible.user.restore');
    Route::post('/users/{user}/force-delete', 'UsersController@forceDelete')->name('permissible.user.force-delete');

    // Roles Management

		/*========================================================
			Role Permission
		=========================================================*/
		Route::get('role', 'RolePermissionController@getIndex')->name('permissible.role.index')->middleware('permission:admins.manage');
		Route::post('role', 'RolePermissionController@postRole')->name('permissible.role.post')->middleware('permission:admins.manage');

		Route::get('role/{role}/permission', 'RolePermissionController@setRolePermissions')
					->middleware('permission:admins.manage')
					->name('permissible.role.permission');

		Route::post('role/{role}/permission', 'RolePermissionController@postRolePermissions')
					->middleware('permission:admins.manage')
					->name('permissible.role.permission.post');
	});
});
