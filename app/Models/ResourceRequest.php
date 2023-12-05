<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ResourceRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];
    const MAIL_TYPE = 'create';
    const FEEDBACK_MAIL_TYPE = 'feedback';
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    protected $appends = ['status_text'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestToUser()
    {
        return $this->belongsTo(User::class, 'request_to');
    }

    public function scopeCreatedByItself($query)
    {
        return $query->where('user_id', auth()->user()->id);
    }

    public function scopeRequestToUser($query)
    {
        return $query->where('request_to', auth()->user()->id);
    }

    public function getStatusTextAttribute()
    {
        if ($this->status == self::APPROVED) {
            return 'Approved';
        } else if($this->status == self::REJECTED) {
            return 'Rejected';
        } else {
            return 'Pending';
        }
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->where('name', 'LIKE', "%{$text['character_search']}%")
            ->orWhereHas('user', function ($q) use ($text) {
                $q->where(
                    DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text['character_search']."%"
                );
            })
            ->orWhereHas('requestToUser', function ($q) use ($text) {
                $q->where(
                    DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text['character_search']."%"
                );
            });
        return $query;
    }

    public function canResourceEditDeleteAndApprovedRejected()
    {

        if($this->status == self::PENDING){
            return true;
        } else{
            return false;
        }
    }
}
