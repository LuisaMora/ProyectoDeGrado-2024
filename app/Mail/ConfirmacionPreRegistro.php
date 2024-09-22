<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacionPreRegistro extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $restaurante;

    public function __construct($usuario, $restaurante)
    {
        $this->usuario = $usuario;
        $this->restaurante = $restaurante;
    }

    public function build()
    {
        return $this->subject('Confirmación de Pre-Registro')
                    ->view('emails.confirmacion_pre_registro');
    }
}
