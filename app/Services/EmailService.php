<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send an email.
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return void
     */
    public function sendEmail(string $to, Mailable $mail): void
    {
        Mail::to($to)->send($mail);
    }
}