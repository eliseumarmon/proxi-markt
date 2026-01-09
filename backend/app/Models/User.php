<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'usuaraios';
    protected $fillable = [
        'nombre_usuario',
        'email',
        'contrasenya',
        'telefono',
    ];
}
