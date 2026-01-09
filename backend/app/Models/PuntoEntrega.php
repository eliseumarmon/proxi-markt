<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PuntoEntrega extends Model
{
    use HasFactory;

    // Nombre de la tabla exacto como en tu SQL
    protected $table = 'puntos_entrega';

    // Definimos las constantes de tiempo según tu base de datos
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_usuario',
        'longitud',
        'latitud',
        'nombre_punto',
        'direccion_punto'
    ];

    /**
     * Relación con el Usuario que creó el punto de entrega (normalmente el vendedor)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Relación con las compraventas que ocurren en este punto
     */
    public function compraventas()
    {
        return $this->hasMany(CompraVenta::class, 'id_punto', 'id');
    }
}