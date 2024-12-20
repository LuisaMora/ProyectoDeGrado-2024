<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistroEmpleado extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $restaurante;

    public function __construct($usuario,$restaurante)
    {
        $this->usuario = $usuario;
        $this->restaurante = $restaurante;
    }

    public function build()
    {
        return $this->subject('Confirmación de Registro')
                    ->view('emails.registro_empleado');
    }

}
