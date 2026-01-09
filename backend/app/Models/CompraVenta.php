<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompraVenta extends Model
{
    use HasFactory;

    // Nombre de la tabla según tu base.sql
    protected $table = 'compraventas';

    // Laravel por defecto busca 'created_at' y 'updated_at'. 
    // Como en tu SQL usas 'modified_at' para las actualizaciones:
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_producto',
        'id_comprador',
        'id_vendedor',
        'id_punto',
        'cantidad_total',
        'estado' // 'pendiente', 'en curso', 'completado', 'cancelado'
    ];

    /**
     * Relación con el Producto reservado/comprado
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    /**
     * Relación con el Usuario que compra
     */
    public function comprador()
    {
        return $this->belongsTo(User::class, 'id_comprador', 'id');
    }

    /**
     * Relación con el Usuario que vende (Agricultor)
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'id_vendedor', 'id');
    }

    /**
     * Relación con el Punto de Entrega acordado
     */
    public function puntoEntrega()
    {
        return $this->belongsTo(PuntoEntrega::class, 'id_punto', 'id');
    }
}