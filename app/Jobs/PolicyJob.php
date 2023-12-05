<?php

namespace App\Jobs;

use App\Mail\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;
use App\Mail\PolicyMail;

class PolicyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $email;
    protected $policy;
    protected $title;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($policy, $email, $title)
    {
        $this->email  = $email;
        $this->policy = $policy;
        $this->title  = $title;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = $this->email;
        $policy = $this->policy;
        $title = $this->title;
        Mail::to($users)->send(new Policy($title, $policy));
    }
}
