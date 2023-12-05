<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "tasks";

    protected $guarded = ['id'];
    
    protected $appends = ['status_text', 'priority_Text', 'billable_text'];

    const BACKLOG = 1;
    const IN_PROGRESS = 2;
    const COMPLETED = 3;
    const QA = 4;
    const CLOSED = 5;
    const CRITICAL = 1;
    const HIGH = 2;
    const MEDIUM = 3;
    const LOW = 4;
    const BILLABLE = 1;
    const NON_BILLABLE = 2;
    

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function taskType() {
        return $this->belongsTo(TaskType::class, 'type', 'id');
    }

    public function project() {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
    
    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('title', 'LIKE', "%{$text['character_search']}%");
        return $query;
    }

    public function getStatusTextAttribute()
    {
        if ($this->status == self::BACKLOG) {
            return 'Backlog';
        } else if($this->status == self::COMPLETED) {
            return 'Completed';
        } else if($this->status == self::QA) {
            return 'QA';
        } else if($this->status == self::CLOSED) {
            return 'Closed';
        } else {
            return "In Progress";
        }
    }

    public function getPriorityTextAttribute()
    {
        if ($this->priority == self::CRITICAL) {
            return 'Critical';
        } else if($this->priority == self::HIGH) {
            return 'High';
        } else if($this->priority == self::MEDIUM) {
            return 'Medium';
        } else {
            return "Low";
        }
    }

    public function getBillableTextAttribute()
    {
        if ($this->billable == self::BILLABLE) {
            return 'Yes';
        } else {
            return "No";
        }
    }

}
