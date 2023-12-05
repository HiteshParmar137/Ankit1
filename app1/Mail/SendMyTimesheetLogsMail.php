<?php

namespace App\Mail;

use App\Exports\MyTimesheetLogsExports;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SendMyTimesheetLogsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $fileName;
    public $myTimesheetLogs;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $subject,
        $myTimesheetLogs,
        $fileName,
        $user
    )
    {
        $this->subject = $subject;
        $this->myTimesheetLogs = $myTimesheetLogs;
        $this->fileName = $fileName;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->attach(
                Excel::download(
                    new MyTimesheetLogsExports($this->myTimesheetLogs,$this->user), 
                    $this->fileName
                )->getFile(), ['as' => $this->fileName]
            )
            ->view('emails.my_timesheet_email');

            
    }
}
