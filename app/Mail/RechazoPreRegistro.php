<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RechazoPreRegistro extends Mailable
{
    use Queueable, SerializesModels;

    public $formPreRegistro;
    public $motivoRechazo;

    public function __construct($formPreRegistro, $motivoRechazo)
    {
        $this->formPreRegistro = $formPreRegistro;
        $this->motivoRechazo = $motivoRechazo;
    }

    public function build()
    {
        return $this->subject('Rechazo de Pre-Registro')
                    ->view('emails.rechazo_pre_registro');
    }
}
