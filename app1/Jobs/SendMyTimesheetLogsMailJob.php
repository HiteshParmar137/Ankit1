<?php

namespace App\Jobs;

use App\Mail\SendEmailVerificationLinkMail;
use App\Mail\SendMyTimesheetLogsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendMyTimesheetLogsMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emails;
    public $subject;
    public $myTimesheetLogs;
    public $fileName;
    public $user;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $emails,
        $subject,
        $myTimesheetLogs,
        $fileName,
        $user
    )
    {
        $this->emails = $emails;
        $this->subject = $subject;
        $this->myTimesheetLogs = $myTimesheetLogs;
        $this->fileName = $fileName;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->emails as $email) {
            Mail::to($email)->send(new SendMyTimesheetLogsMail(
                $this->subject,
                $this->myTimesheetLogs,
                $this->fileName,
                $this->user
            ));
        }
    }

    public function failed(Throwable $e)
    {
        Log::error("error is " . $e->getMessage());
    }
}
