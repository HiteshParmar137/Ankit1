<?php

namespace App\Traits\LocalQueryScopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait TimesheetLogQueryScope {

    public function scopeOnlyForThisTimesheet(Builder $query, int $timesheetId): Builder
    {
        return $query->where('timesheet_id', $timesheetId);
    }

    public function scopeOnlyForThisUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOnlyForThisYear(Builder $query): Builder
    {
        return $query->whereYear('date', Carbon::parse(Carbon::now())->format('Y'));
    }
}