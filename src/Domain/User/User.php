<?php

declare(strict_types=1);

namespace KBuilder\Domain\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $table      = 'kb_users';
    protected $primaryKey = 'id';
    public    $timestamps = false; // handle manually

    protected $fillable = [
        'uuid', 'name', 'email', 'password', 'avatar', 'status',
        'email_verified_at', 'last_login_at', 'meta', 'created_at', 'updated_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'meta'               => 'array',
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
        'created_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];
}
