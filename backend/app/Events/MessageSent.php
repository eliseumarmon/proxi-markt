<?php

namespace App\Events;

use App\Models\Mensajes;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

//el shouldbroadcast fa que el evento no es quede soles en laravel
//que tambe senvie al front
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(Mensajes $mensaje)
    {
        $this->mensaje = $mensaje;
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->mensaje->id_chat);
    }
}