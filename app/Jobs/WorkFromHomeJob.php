<?php

namespace App\Jobs;

use App\Mail\WorkFromHomeMail;
use App\Models\WorkFromHome;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Mail;

class WorkFromHomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $title;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $title, $type)
    {
        $this->data = $data;
        $this->title = $title;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $workFromHomeData = $this->data->toArray();
            if ($this->type == WorkFromHome::WFH_MAIL_TYPE) {
                $mailData = [
                    'request_to_user_name' => $this->data->requestToUser->name ?? '',
                    'user_name' => $this->data->user->name ?? '',
                    'to_email' => $this->data->requestToUser->email ?? ' ',
                    'from_email' => $this->data->user->email ?? '',
                    'wfh_mail_type' => $this->type,
                    'title' => $this->title,
                ];
                $newData = array_merge($mailData, $workFromHomeData);
            } else {
                $mailData = [
                    'request_to_user_name' => $this->data->requestToUser->name ?? 'N/A',
                    'user_name' => $this->data->user->name ?? 'N/A',
                    'to_email' => $this->data->user->email ?? 'N/A',
                    'from_email' => $this->data->requestToUser->email ?? 'N/A',
                    'wfh_mail_type' => $this->type,
                    'title' => $this->title,
                ];
                $newData = array_merge($mailData, $workFromHomeData);
            }
            Mail::to($newData['to_email'])->send(new WorkFromHomeMail($newData));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
