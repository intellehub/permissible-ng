<?php

namespace Shahnewaz\PermissibleNg\Facades;

use Illuminate\Support\Facades\Facade;

class PermissibleAuth extends Facade
{
    protected static function getFacadeAccessor () {
        return 'permissible.auth';
    }
}
