<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class Notificacion extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    // Define los canales de notificación
    public function via($notifiable)
    {
        // Utiliza solo el canal de base de datos
        return ['database'];
    }

    // Define la representación en array de la notificación
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
        ];
    }
}
