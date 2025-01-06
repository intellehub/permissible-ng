<?php
namespace Shahnewaz\PermissibleNg\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Shahnewaz\PermissibleNg\Contracts\PermissibleAuthInterface;

class PermissibleService implements PermissibleAuthInterface 
{
    public function authenticate(Request $request): JsonResponse 
    {
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

    public function logout(): JsonResponse
    {
        auth()->logout(true);
        return response()->json(['success' => 'Logged out.'], 200);
    }
}
