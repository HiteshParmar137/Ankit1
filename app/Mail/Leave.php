<?php

namespace App\Mail;

use App\Models\Leaves;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class Leave extends Mailable
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
        Log::info('LeaveMail -> mailstart');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->data['leave_mail_type'] == Leaves::LEAVE_MAIL_TYPE) {
            return $this->from($this->data['from_email'])
                ->subject('Leave' . ' - ' . $this->data['title'])
                ->view('emails.leave_mail')
                ->with([
                    'leaveDetails' => $this->data,
                ]);
            Log::info('LeaveMail->end');
        } else {
            return $this->from($this->data['from_email'])
                ->subject('Leave' . ' - ' . $this->data['title'])
                ->view('emails.leave_feedback_mail')
                ->with([
                    'leaveDetails' => $this->data,
                ]);
        }
    }
}
