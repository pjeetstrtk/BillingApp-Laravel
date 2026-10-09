<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            // Assigning a plain-text password hashes it automatically
            // (already-hashed values, e.g. imported bcrypt hashes, are left as-is).
            'password' => 'hashed',
        ];
    }
}
