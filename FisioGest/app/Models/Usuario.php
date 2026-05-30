<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table      = 'usuarios';
    protected $primaryKey = 'usuario_id';

    protected $fillable = [
        'nombre',
        'correo',
        'contrasena',
        'rol',
    ];

    protected $hidden = ['contrasena', 'recordar_token'];

    // Indica a Laravel qué campo es la contraseña
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}
