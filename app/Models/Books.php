<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Books extends Model
{
    use HasFactory; use SoftDeletes;
    
    protected $guarded = ['id'];

    const AVAILABLE = 1;
    const UNAVAILABLE = 0;

    protected $appends = ['status_text'];

    public function getStatusTextAttribute()
    {
        if ($this->status == self::AVAILABLE) {
            return 'AVAILABLE';
        } else {
            return 'UNAVAILABLE';
        }
    }

    public function requestToReadBook(){
        return $this->hasMany(BookReadRequest::class, 'book_id');
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('name', 'LIKE', "%{$text}%");

        return $query;
    }
}
