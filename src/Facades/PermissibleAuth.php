<?php

namespace Shahnewaz\PermissibleNg\Facades;

use Illuminate\Support\Facades\Facade;
use Shahnewaz\PermissibleNg\Contracts\PermissibleAuthInterface;

/**
 * @method static \Illuminate\Http\JsonResponse authenticate(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Http\JsonResponse logout()
 * @see \Shahnewaz\PermissibleNg\Services\PermissibleService
 */
class PermissibleAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'permissible.auth';
    }
}
