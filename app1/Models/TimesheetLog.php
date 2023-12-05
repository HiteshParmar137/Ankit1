<?php

namespace App\Models;

use App\Traits\LocalQueryScopes\TimesheetLogQueryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimesheetLog extends Model
{
    use HasFactory, SoftDeletes, TimesheetLogQueryScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * @return BelongsTo
     */
    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class, 'timesheet_id', 'id');
    }

    public function holiday(): HasOne
    {
        return $this->hasOne(Holiday::class,'date','date');
    }
}
