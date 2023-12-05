<?php

namespace App\Jobs;

use App\Mail\SendEmailVerificationLinkMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationLinkMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userDetails;

    public $emailVerifyUrl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $url)
    {
        $this->userDetails = $user;

        $this->emailVerifyUrl = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Mail::to($this->userDetails->email)->send(
            new SendEmailVerificationLinkMail(
                $this->userDetails,
                $this->emailVerifyUrl
            )
        );
    }
}
