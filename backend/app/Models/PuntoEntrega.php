<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Producto;

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

    public function usuario() {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function productos() {
        return $this->hasMany(Producto::class, 'id_puntoentrega');
    }

    public function compraventas()
    {
        return $this->hasMany(CompraVenta::class, 'id_punto', 'id');
    }
}