<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;   // ← esta línea

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;   // ← agregar HasApiTokens aquí

    protected $fillable = [
    'name',
    'email',
    'password',
    'rol',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}