<?php

namespace App\Jobs;

use App\Mail\InviteUser;
use App\Mail\Leave;
use App\Models\Leaves;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Mail;

class InviteUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $token;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $token)
    {
        $this->data = $data;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = [
                'data'  => $this->data,
                'token' => $this->token,
            ];
            $email = $this->data['email'];
            Mail::to($email)->send(new InviteUser($data));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
