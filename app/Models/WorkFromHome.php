<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkFromHome extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    const FULL_DAY = 1;
    const HALF_DAY = 2;
    const FIRST_HALF = 1;
    const SECOND_HALF = 2;
    const PENDING = 'Pending';
    const REJECTED = 'Rejected';
    const APPROVED = 'Approved';
    const WFH_MAIL_TYPE = 'leaveMail';
    const WFH_FEED_BACK_MAIL_TYPE = 'leaveFeedBackMail';

    protected $appends = ['wfh_type_text', 'half_day_text'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 
    public function requestToUser()
    {
        return $this->belongsTo(User::class, 'request_to');
    }

    public function scopeCreatedByItself($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeRequestToUser($query)
    {
        return $query->where('request_to', Auth::id());
    }

    public function getwfhTypeTextAttribute()
    {
        if ($this->type == self::FULL_DAY) {
            return 'Full Day';
        } else {
            return 'Half Day';
        }
    }

    public function getHalfDayTextAttribute()
    {
        if ($this->half_day == self::FIRST_HALF && $this->half_day != NULL) {
            return 'First Half';
        } elseif ($this->half_day == self::SECOND_HALF && $this->half_day != NULL) {
            return 'Second half';
        } else {
            return NULL;
        }
    }

    public function canWfhEditDelete()
    {

        if ($this->start_date >= Carbon::now()->format('Y-m-d') || (($this->start_date >= Carbon::now()->format('Y-m-d') && $this->status == self::APPROVED))) {
            print_r($this->start_date, 'if');
            return true;
        } else {
            print_r($this->start_date, 'else');
            return false;
        }
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->whereHas('user',function($query) use($text){
                $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text['character_search']."%");
            });
        return $query;
    }
}
