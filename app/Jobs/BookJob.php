<?php

namespace App\Jobs;

use App\Mail\BookMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class BookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $data;
    protected $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $type)
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
            $users = User::role(User::HUMAN_RESOURCE_EXECUTIVE_ROLE)->get(); // If a new HR position is added, the user [User::HUMAN_RESOURCE_EXECUTIVE_ROLE, User::roleName] will be used.
            foreach($users as $user){
                Mail::to($user->email)->send(new BookMail($this->data, $this->type));
            }
                
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
