<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmailVerificationLinkMail extends Mailable
{
    use Queueable, SerializesModels;
    public $emailVerificationLink;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $emailVerificationLink)
    {
        $this->user = $user;
        $this->emailVerificationLink = $emailVerificationLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Email Verification')
            ->view('emails.email_verification');
    }
}
