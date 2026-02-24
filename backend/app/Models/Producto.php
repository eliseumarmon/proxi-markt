<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\PuntoEntrega;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_categoria',
        'id_usuario',
        'id_puntoentrega', 
        'nombre_producto',
        'descripcion',
        'precio',
        'stock_total',
        'imagen',
        'estado'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function usuario(){
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function punto_entrega(){
        return $this->belongsTo(PuntoEntrega::class, 'id_puntoentrega', 'id');
    }
}