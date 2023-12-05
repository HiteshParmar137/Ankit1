<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    const PIPELINE = 1;
    const IN_PROGRESS = 2;
    const COMPLETED = 3;
    const CLOSED = 4;
    const HOLD = 5;

    protected $guarded = ['id'];

    protected $appends = ['project_status_text'];


    public function client() {
        return $this->belongsTo(Clients::class, 'client_id', 'id');
    }

    public function technologies()
    {
        return $this->hasMany(ProjectTechnology::class, 'project_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id', 'id');
    }

    public function assignee()
    {
        return $this->hasMany(ProjectAssignee::class, 'project_id', 'id');
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('name', 'LIKE', "%{$text['character_search']}%");
        return $query;
    }

    public function getProjectStatusTextAttribute()
    {
        if ($this->status == self::PIPELINE) {
            return 'Pipeline';
        } else if($this->status == self::COMPLETED) {
            return 'Completed';
        } else if($this->status == self::CLOSED) {
            return 'Closed';
        } else if($this->status == self::HOLD) {
            return 'Hold';
        } else {
            return "In Progress";
        }
    }

    public function inProgressTasks()
    {
    	return $this->hasMany(ProjectTask::class)->where('status', ProjectTask::IN_PROGRESS);
    }

    public function qATasks()
    {
    	return $this->hasMany(ProjectTask::class)->where('status', ProjectTask::QA);
    }

    public function closedTasks()
    {
    	return $this->hasMany(ProjectTask::class)->where('status', ProjectTask::CLOSED);
    }
}
