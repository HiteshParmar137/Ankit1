<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteUser extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
   
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        try {
            return $this->from(config("constant.FROM_EMAIL_ADDRESS"), config("constant.FROM_EMAIL_NAME"))
                ->subject('Invite')
                ->view('emails.invite_user')
                ->with([
                    'inviteUser' => $this->data,
                ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
