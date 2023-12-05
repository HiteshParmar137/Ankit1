<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PunchLogs extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];


    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    
    public function punchLogTimes(){

        return $this->hasMany(PunchLogTimes::class, 'punch_logs_id', 'id');
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->whereHas('user',function($query) use($text){
                $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text."%");
            });
        return $query;
    }
}
