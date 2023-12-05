<?php

namespace App\Observers;

use App\Models\PunchLogs;

class PunchLogObserver
{
   
    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\PunchLogs  $user
     * @return void
     */
    public function created(PunchLogs $PunchLogs)
    {
        
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\PunchLogs  $user
     * @return void
     */
    public function updated(PunchLogs $PunchLogs)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\PunchLogs  $user
     * @return void
     */
    public function deleted(PunchLogs $punchLogs)
    {
        $punchLogs->punchLogTimes()->delete();
    }
}
