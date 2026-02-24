<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Mensajes;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Events\NotificacionUpdate;

class MensajesController extends Controller
{
    // Función para guardar y enviar un nuevo mensaje
    public function store(Request $request) {
        // 1. Verificamos que nos llegan todos los datos necesarios y que son correctos
        $request->validate([
            'id_vendedor' => 'required|exists:usuarios,id', // El vendedor debe existir
            'id_producto' => 'required|exists:productos,id', // El producto debe existir
            'contenido' => 'required|string' // El mensaje debe tener texto
        ]);

        // Guardamos los datos importantes en variables para que sea más fácil usarlos luego
        $authId = $request->user()->id; // ID del usuario que está escribiendo el mensaje
        $receptorId = $request->id_vendedor; // ID del usuario que va a recibir el mensaje
        $productoId = $request->id_producto; // ID del producto sobre el que están hablando

        /* 2. Buscamos si ya existe un chat previo para este producto entre estas dos personas.
        Esto sirve para diferenciar los roles en el chat, sin importar quién empezó a hablar. */
        $chat = Chat::where('id_producto', $productoId)
            ->where(function ($query) use ($authId, $receptorId) {
                // Comprobamos las dos combinaciones posibles:
                $query->where(function ($q) use ($authId, $receptorId) {
                    // Opción A: Tú eres el comprador y la otra persona es el vendedor
                    $q->where('id_comprador', $authId)->where('id_vendedor', $receptorId);
                })->orWhere(function ($q) use ($authId, $receptorId) {
                    // Opción B: Tú eres el vendedor y la otra persona es el comprador
                    $q->where('id_comprador', $receptorId)->where('id_vendedor', $authId);
                });
            })->first(); // Nos quedamos con el primer chat que coincida

        // 3. Si no existe ningún chat previo, lo creamos desde cero
        if (!$chat) {
            $chat = Chat::create([
                'id_comprador' => $authId, // El que envía el primer mensaje es el comprador
                'id_vendedor' => $receptorId, // El que lo recibe es el vendedor
                'id_producto' => $productoId, // El producto en cuestión
            ]);
        }

        // 4. Creamos el mensaje en la base de datos y lo unimos al chat correspondiente
        $mensaje = Mensajes::create([
            'id_chat' => $chat->id, // ID del chat que hemos encontrado o creado
            'id_envio' => $authId, // ID del usuario que manda este mensaje
            'contenido' => $request->contenido, // El texto del mensaje
        ]);

        /* 5. Mensajes en tiempo real
        // 'broadcast' hace que el mensaje se envíe por internet al instante sin pulsar F5.
        Le pasamos el evento 'MessageSent' con toda la información del mensaje y de quién lo envía 'emisor'.
        'toOthers()' asegura que la notificación solo le salte a la OTRA persona, no a ti que acabas de enviarlo. */
        broadcast(new MessageSent($mensaje->load('emisor')))->toOthers();

        // 6. Devolvemos una respuesta confirmando que todo ha ido bien
        return response()->json([
            'status' => 'success',
            'chat_id' => $chat->id,
            'mensaje' => $mensaje
        ]);
    }
}