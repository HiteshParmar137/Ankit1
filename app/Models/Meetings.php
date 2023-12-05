<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meetings extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];
    
    protected $appends = ['status_name'];

    const SCHEDULED = 1;
    const CANCELLED = 2;
    const COMPLETED = 3;
    const OVERDUE   = 4;

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('title', 'LIKE', "%{$text['character_search']}%")
            ->orWhere('date_time', 'LIKE', "%{$text['character_search']}%");
        return $query;
    }

    public function getStatusNameAttribute()
    {
        $status = $this->attributes['status'] ?? self::SCHEDULED;
        $statusName = self::SCHEDULED;

        switch ($status) {
            case self::SCHEDULED:
                $statusName = 'Scheduled';
                break;
            case self::CANCELLED:
                $statusName = 'Cancelled';
                break;
            case self::COMPLETED:
                $statusName = 'Completed';
                break;
            case self::OVERDUE:
                $statusName = 'Overdue';
                break;
        }
        return $statusName;
    }
}
