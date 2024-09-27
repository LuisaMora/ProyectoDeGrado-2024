<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $direccion_front;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $direccion_front)
    {
        $this->token = $token;
        $this->direccion_front = $direccion_front;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Restablece tu contraseña')
                    ->view('emails.reset_password');
                    // ->with(['token' => $this->token, 'direccion_front' => $this->direccion_front]);
    }
}
