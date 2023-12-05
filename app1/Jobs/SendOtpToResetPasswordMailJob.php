<?php

namespace App\Jobs;

use App\Mail\SendOtpToResetPasswordMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOtpToResetPasswordMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userDetails;

    public $otpToVerify;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $otp)
    {
        $this->userDetails = $user;

        $this->otpToVerify = $otp;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->userDetails->email)->send(
            new SendOtpToResetPasswordMail(
                $this->userDetails,
                $this->otpToVerify
            )
        );
    }
}
