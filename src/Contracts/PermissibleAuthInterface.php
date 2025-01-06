<?php

namespace Shahnewaz\PermissibleNg\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface PermissibleAuthInterface
{
    public function authenticate(Request $request): JsonResponse;
    public function logout(): JsonResponse;
} 