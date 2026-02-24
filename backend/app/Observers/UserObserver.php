<?php

namespace App\Observers;

use App\Models\Mensajes;

class UserObserver
{
    /**
     * Handle the Mensajes "created" event.
     */
    public function created(Mensajes $mensajes): void
    {
        //
    }

    /**
     * Handle the Mensajes "updated" event.
     */
    public function updated(Mensajes $mensajes): void
    {
        //
    }

    /**
     * Handle the Mensajes "deleted" event.
     */
    public function deleted(Mensajes $mensajes): void
    {
        //
    }

    /**
     * Handle the Mensajes "restored" event.
     */
    public function restored(Mensajes $mensajes): void
    {
        //
    }

    /**
     * Handle the Mensajes "force deleted" event.
     */
    public function forceDeleted(Mensajes $mensajes): void
    {
        //
    }
}
