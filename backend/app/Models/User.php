<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    //activa el metodo createtoken
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'id';
    protected $table = 'usuarios';
    protected $fillable = [
        'nombre_usuario',
        'email',
        'contrasenya',
        'telefono',
    ];

    //esta es la columna clave que el createtoken es fija
    public function getAuthPassword()
    {
        return $this->contrasenya;
    }
}
