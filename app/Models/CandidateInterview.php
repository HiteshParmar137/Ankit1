<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CandidateInterview extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    
    const STATUS_PASS = 1;
    const STATUS_FAIL = 0;

    protected $appends = ['status_text'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function candidateDetails()
    {
    	return $this->belongsTo(CandidateDetail::class, 'candidate_id');
    }

    public function interviewstage()
    {
        return $this->belongsTo(InterviewStage::class, 'interview_stages_id');
    }

    public function getStatusTextAttribute()
    {
        if ($this->status == self::STATUS_PASS) {
            return 'Pass';
        } else {
            return 'Fail';
        }
    }
    

}
