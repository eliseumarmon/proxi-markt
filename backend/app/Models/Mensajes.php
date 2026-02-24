<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensajes extends Model
{
    protected $table = 'mensajes';

    // IMPORTANTE: He añadido 'leido' aquí. 
    // Sin esto, no podrás cambiar el estado de no leído a leído.
    protected $fillable = ['id_chat', 'id_envio', 'contenido', 'leido'];

    // Opcional: Esto ayuda a que cuando envíes el JSON al frontend, 
    // llegue como true/false en lugar de 1/0.
    protected $casts = [
        'leido' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'id_chat');
    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'id_envio');
    }
}
