<?php

namespace App\Repositories;

use App\Jobs\LeaveJob;
use App\Models\Leaves;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Log;

class BaseRepository
{
    // get all jobOpenings
    public function lists()
    {
        return "demo";
    }
}
