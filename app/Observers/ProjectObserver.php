<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
   
    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\Project  $user
     * @return void
     */
    public function created(Project $Project)
    {
        
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\Project  $user
     * @return void
     */
    public function updated(Project $Project)
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\Project  $user
     * @return void
     */
    public function deleted(Project $project)
    {
        $project->technologies()->delete();
    }
}
