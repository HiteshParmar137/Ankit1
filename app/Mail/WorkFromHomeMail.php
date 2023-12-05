<?php

namespace App\Mail;

use App\Models\WorkFromHome;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class WorkFromHomeMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;
    protected $title;
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
        if ($this->data['wfh_mail_type'] == WorkFromHome::WFH_MAIL_TYPE) {
            return $this->from($this->data['from_email'])
                ->subject('Work From Home' . ' - ' . $this->data['title'])
                ->view('emails.work_from_home_mail')
                ->with([
                    'wfhDetails' => $this->data,
                ]);
        } else {
            return $this->from($this->data['from_email'])
                ->subject('Work From Home' . ' - ' . $this->data['title'])
                ->view('emails.work_from_home_feedback_mail')
                ->with([
                    'wfhDetails' => $this->data,
                ]);
        }
    }
}
