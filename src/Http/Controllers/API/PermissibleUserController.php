<?php

namespace Shahnewaz\PermissibleNg\Http\Controllers\API;

use Illuminate\Http\Request;
use Shahnewaz\PermissibleNg\Facades\PermissibleAuth;

class PermissibleUserController
{

    public function postAuthenticate(Request $request): \Illuminate\Http\JsonResponse
    {
        return PermissibleAuth::authenticate($request);
    }

    protected function logout() {
        return PermissibleAuth::logout();
    }
}
