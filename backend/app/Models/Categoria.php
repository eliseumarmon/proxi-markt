<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias'; 

    protected $fillable = [
        'nombre_categoria'
    ];
    
    protected $hidden = ["updated_at", "created_at"];

   
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria');
    }
}