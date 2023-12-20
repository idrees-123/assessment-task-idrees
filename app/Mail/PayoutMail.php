<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected float $amount)
    {
    }

    public function build()
    {
        return $this->view('emails.payout', ['amount' => $this->amount]);
    }
}
