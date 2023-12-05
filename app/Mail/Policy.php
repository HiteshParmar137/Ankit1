<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Policy extends Mailable
{
    use Queueable, SerializesModels;
    protected $policy;
    protected $title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, $policy)
    {
        $this->title = $title;
        $this->policy = $policy;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config("constant.FROM_EMAIL_ADDRESS"), config("constant.FROM_EMAIL_NAME"))
            ->subject('Policy' . ' - ' . $this->title)
            ->view('emails.policy_mail')
            ->with([
                'policy_details' => $this->policy,
            ]);
    }
}
