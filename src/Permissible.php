<?php

namespace Shahnewaz\PermissibleNg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Shahnewaz\PermissibleNg\Traits\Permissible as PermissibleTrait;


class Permissible extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, PermissibleTrait, SoftDeletes;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
