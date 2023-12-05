<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookReadRequest extends Model
{
    use HasFactory; use SoftDeletes;

    protected $guarded = ['id'];

    const PENDING = 0;
    const GIVEN = 1;
    const DECLINED = 2;
    const TYPE = 'requestToReadBook';

    protected $appends = ['status_text'];

    public function getStatusTextAttribute()
    {
        if ($this->status == self::PENDING) {
            return 'PENDING';
        } elseif($this->status == self::GIVEN) {
            return 'GIVEN';
        } else {
            return 'DECLINED';
        }
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('book_id', 'LIKE', "%{$text}%");

        return $query;
    }

    public function book()
    {
        return $this->belongsTo(Books::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function statusBy() {
        return $this->belongsTo(User::class, 'status_by');
    } 
}
