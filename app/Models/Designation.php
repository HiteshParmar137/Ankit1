<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->where('title', 'LIKE', "%{$text['character_search']}%")
            ->orWhereHas('department', function ($q) use ($text) {
                $q->where(
                    'department_name',
                    'LIKE',
                    "%{$text['character_search']}%"
                );
            });
        return $query;
    }
}