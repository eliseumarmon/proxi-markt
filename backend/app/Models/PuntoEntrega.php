<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PuntoEntrega extends Model
{
    use HasFactory;

    protected $table = 'puntos_entrega';


    protected $fillable = [
        'id_usuario',
        'longitud',
        'latitud',
        'nombre_punto',
        'direccion_punto'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }


    public function compraventas()
    {
        return $this->hasMany(CompraVenta::class, 'id_punto', 'id');
    }
}