<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompraVenta extends Model
{
    use HasFactory;

    protected $table = 'compraventas';


    protected $fillable = [
        'id_producto',
        'id_comprador',
        'id_vendedor',
        'id_punto',
        'cantidad_total',
        'estado' 
    ];

    
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    public function comprador()
    {
        return $this->belongsTo(User::class, 'id_comprador', 'id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'id_vendedor', 'id');
    }

    public function puntoEntrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'id_punto', 'id');
    }
}