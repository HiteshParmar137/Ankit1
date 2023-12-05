<?php

namespace App\Mail;

use App\Exports\ManageTimesheetLogsExports;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SendManageTimesheetLogsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $fileName;
    public $manageTimesheetLogs;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $subject,
        $manageTimesheetLogs,
        $fileName,
        $user
    )
    {
        $this->subject = $subject;
        $this->manageTimesheetLogs = $manageTimesheetLogs;
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
                    new ManageTimesheetLogsExports($this->manageTimesheetLogs,$this->user), 
                    $this->fileName
                )->getFile(), ['as' => $this->fileName]
            )
            ->view('emails.manage_timesheet_email');

            
    }
}
