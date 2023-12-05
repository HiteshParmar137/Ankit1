<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewBookRequest extends Model
{
    use HasFactory; use SoftDeletes;

    const TYPE = 'newBookRequest';

    protected $guarded = ['id'];

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('name', 'LIKE', "%{$text}%");

        return $query;
    }

    public function user() {
        return $this->belongsTo(User::class, 'request_by');
    }

    public function scopeCreatedByItself($query)
    {
        return $query->where('request_by', auth()->user()->id);
    }
}
