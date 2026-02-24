<?php

// Importamos los modelos y herramientas de Laravel que vamos a necesitar
namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use App\Models\Mensajes; 
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Función para mostrar la lista de todos los chats del usuario
    public function index(Request $request)
    {
        // Obtenemos el ID del usuario que ha iniciado sesión actualmente
        $usuarioId = $request->user()->id;

        // Buscamos en la base de datos y devolvemos los chats que cumplan lo siguiente:
        return Chat::where(function($query) use ($usuarioId) {
            $query->where('id_comprador', $usuarioId) // El usuario actual debe ser el comprador del chat
                ->orWhere('id_vendedor', $usuarioId); // O debe ser el vendedor.
        })

        // Además del chat, cargamos información relacionada como los producto, el comprador y el vendedor
        ->with(['producto', 'comprador', 'vendedor', 'mensajes' => function($q) {
            // De los mensajes del chat, solo nos traemos el último para mostrar una vista previa
            $q->latest()->limit(1); 
        }])
        
        // Contamos cuántos mensajes hay sin leer y guardamos el número en 'mensajes_no_leidos'
        ->withCount(['mensajes as mensajes_no_leidos' => function ($query) use ($usuarioId) {
            // Un mensaje cuenta como no leído si su estado 'leido' es falso
            $query->where('leido', false)
                  // Y si NO lo envió el usuario actual
                  ->where('id_envio', '!=', $usuarioId);
        }])

        // Ordenamos la lista para que los chats con actividad reciente salgan arriba del todo
        ->orderBy('updated_at', 'desc') 
        // Ejecutamos la consulta y obtenemos los resultados
        ->get();
    }

    // Función para entrar a ver un chat específico y todos sus mensajes
    public function show($id)
    {
        /* Buscamos el chat por su ID. Y cogemos todos los datos relacionados con otras tablas.
        Si el chat no existe, dará un error automático (findOrFail). */
        $chat = Chat::with('mensajes.emisor', 'producto', 'comprador', 'vendedor')->findOrFail($id);
        
        // Medida de seguridad: comprobamos si el usuario actual NO es el comprador ni el vendedor
        if (Auth::id() !== $chat->id_comprador && Auth::id() !== $chat->id_vendedor) {
            // Si es un intruso, le bloqueamos el paso y devolvemos un error de "No autorizado"
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Si es uno de los participantes válidos, le mostramos toda la información del chat
        return $chat;
    }

    // Función para marcar los mensajes de un chat como "leídos"
    public function marcarLeido($id)
    {
        // Volvemos a obtener el ID del usuario actual
        $usuarioId = Auth::id();
        
        // Buscamos en la tabla de Mensajes aquellos que cumplan esto:
        Mensajes::where('id_chat', $id) // Que pertenezcan a este chat específico (id del chat)
            ->where('id_envio', '!=', $usuarioId) // Que NO los haya enviado el usuario actual
            ->where('leido', false) // Que todavía estén marcados como no leídos (false)
            ->update(['leido' => true]); // Y los actualizamos todos a leídos (true)

        // Devolvemos una confirmación rápida de que la acción se ha realizado con éxito
        return response()->json(['success' => true]);
    }
}