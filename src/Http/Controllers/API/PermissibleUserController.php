<?php

namespace Shahnewaz\PermissibleNg\Http\Controllers\API;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissibleUserController
{

    protected function postAuthenticate(Request $request): \Illuminate\Http\JsonResponse
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error logging in'], 500);
        }
        return response()->json(compact('token'));
    }

    protected function logout(Request $request) {
        auth()->logout(true);
        return response()->json(['success' => 'Logged out.'], 200);
    }
}
