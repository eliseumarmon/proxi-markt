<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Incidencias extends Model
{
    protected $table = 'incidencias';

    protected $fillable = [
        'id_usuario',
        'mensaje',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
