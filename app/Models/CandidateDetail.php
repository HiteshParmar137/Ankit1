<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CandidateDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['full_name', 'eligible_for_future_hiring_text'];

    const ELIGIBLE_FOR_FUTURE_HIRING_NO = 0;
    const ELIGIBLE_FOR_FUTURE_HIRING_YES = 1;
    const ELIGIBLE_FOR_FUTURE_HIRING_NOT_SURE = 2;

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text."%");
        return $query;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }

    public function getEligibleForFutureHiringTextAttribute()
    {
        if ($this->eligible_for_future_hiring == self::ELIGIBLE_FOR_FUTURE_HIRING_YES) {
            return 'Yes';
        } elseif ($this->eligible_for_future_hiring == self::ELIGIBLE_FOR_FUTURE_HIRING_NOT_SURE) {
            return 'Not Sure';
        } else {
            return 'No';
        }
    }

    public function jobOpening()
    {
        return $this->belongsTo(JobOpenings::class, 'position_id');
    }

    public function interviews()
    {
        return $this->hasMany(CandidateInterview::class, 'candidate_id', 'id');
    }
}
