<?php

namespace App\Jobs;

use App\Mail\ResourceRequest as MailResourceRequest;
use App\Models\ResourceRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class ResourceRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected $data, protected $type)
    {
        $this->data = $data;
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
            $resourceRequestData = $this->data->toArray();
            if ($this->type == ResourceRequest::MAIL_TYPE) {
                $toMailUser = User::role(User::SUPER_ADMIN_ROLE)->pluck('email')->toArray();
                array_push($toMailUser, $this->data->requestToUser->email);
                $mailData = [
                    'name' => $this->data->name ?? '',
                    'requester' => $this->data->user->name ?? '',
                    'date' => $this->data->requestToUser->created_at ?? '',
                    'from_email' => $this->data->user->email ?? '',
                    'mail_type' => $this->type,
                ];
                $newData = array_merge($mailData, $resourceRequestData);
                Mail::to($toMailUser)->send(new MailResourceRequest($newData));
            } else {
                $mailData = [
                    'name' => $this->data->name ?? '',
                    'requester' => $this->data->requestToUser->name ?? '',
                    'date' => $this->data->requestToUser->created_at ?? '',
                    'status' => $this->data->status_text ?? '',
                    'reason' => $this->data->reason ?? '',
                    'from_email' => $this->data->requestToUser->email ?? '',
                    'to_email' => $this->data->user->email ?? '',
                    'mail_type' => $this->type,
                    'user_name' => $this->data->user->name,
                ];
                $newData = array_merge($mailData, $resourceRequestData);
                Mail::to($newData['to_email'])->send(new MailResourceRequest($newData));
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
