<?php

	function permissibleMenu () {
    $permissibleConfig = file_exists(base_path('config/permissible.php')) ? include(base_path('config/permissible.php')) : include(__DIR__.'../../config/permissible.php');
    
    if ($permissibleConfig['enable_routes'] === true) {
      $withUserRoutes = $permissibleConfig['enable_user_management_routes'];
      return permissibleRoutesArray($withUserRoutes);
    } 
    return [];
	}

	function permissibleRoutesArray ($withUserRoutes) {
		$routes = [
			'Permissible',
		  [
          'text'        => 'Roles',
          'route'       => 'permissible.role.index',
          'icon'        => 'icon-shield',
      ],
/*      [
          'text'        => 'Permissions',
          'route'       => 'permissible.permission.index',
          'icon'        => 'unlock',
      ],*/
		];

		$userManagementRoutes = [
        'text'        => 'Users',
        'route'       => 'permissible.user.index',
        'icon'        => 'icon-users',
    ];

    if ($withUserRoutes)  {
    	$routes[] = $userManagementRoutes;
    }

    return $routes;
	}