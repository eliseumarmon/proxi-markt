<?php

// Importamos las herramientas y modelos necesarios de Laravel
namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Mensajes;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MensajesController extends Controller
{
    // Función para guardar y enviar un nuevo mensaje
    public function store(Request $request) {
        // 1. Verificamos que nos llegan datos válidos
        $request->validate([
            'id_chat' => 'nullable|exists:chats,id',
            'id_vendedor' => 'required_without:id_chat|exists:usuarios,id',
            'id_producto' => 'required_without:id_chat|exists:productos,id',
            'contenido' => 'required|string'
        ]);

        $authId = $request->user()->id;
        $contenido = trim((string) $request->contenido);

        if ($contenido === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'El mensaje no puede estar vacío.'
            ], 422);
        }

        $chat = null;

        // 2. Si llega id_chat, usamos ese chat validando que el usuario participa.
        if ($request->filled('id_chat')) {
            $chat = Chat::findOrFail($request->id_chat);

            if ((int) $chat->id_comprador !== (int) $authId && (int) $chat->id_vendedor !== (int) $authId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado para enviar mensajes en este chat.'
                ], 403);
            }
        }

        // 3. Si no llega id_chat, buscamos/creamos chat por producto + usuarios.
        if (!$chat) {
            $receptorId = (int) $request->id_vendedor;
            $productoId = (int) $request->id_producto;

            $chat = Chat::where('id_producto', $productoId)
                ->where(function ($query) use ($authId, $receptorId) {
                    $query->where(function ($q) use ($authId, $receptorId) {
                        $q->where('id_comprador', $authId)->where('id_vendedor', $receptorId);
                    })->orWhere(function ($q) use ($authId, $receptorId) {
                        $q->where('id_comprador', $receptorId)->where('id_vendedor', $authId);
                    });
                })->first();

            if (!$chat) {
                $chat = Chat::create([
                    'id_comprador' => $authId,
                    'id_vendedor' => $receptorId,
                    'id_producto' => $productoId,
                ]);
            }
        }

        // 4. Creamos el mensaje en BD
        $mensaje = Mensajes::create([
            'id_chat' => $chat->id,
            'id_envio' => $authId,
            'contenido' => $contenido,
        ]);

        // 5. Emitimos evento en tiempo real sin romper la petición si falla el broadcaster.
        try {
            broadcast(new MessageSent($mensaje->load('emisor')))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('No se pudo emitir MessageSent', [
                'chat_id' => $chat->id,
                'mensaje_id' => $mensaje->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 6. Respuesta OK
        return response()->json([
            'status' => 'success',
            'chat_id' => $chat->id,
            'mensaje' => $mensaje
        ]);
    }
}
