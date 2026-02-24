<?php

use App\Models\Chat;

Broadcast::channel('chat.{id_chat}', function ($user, $id_chat) {
    $chat = Chat::find($id_chat);

    if (!$chat) return false;

    // soles els integrants de eixe chat poden entrar
    return (int) $user->id === (int) $chat->id_comprador || 
           (int) $user->id === (int) $chat->id_vendedor;
});