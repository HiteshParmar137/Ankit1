<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function scopeCreatedByItself($query)
    {
        return $query->where('sent_by', auth()->user()->id);
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('first_name', 'LIKE', "%{$text['character_search']}%")
        ->orWhere(
            'last_name',
            'like',
            "%{$text['character_search']}%"
        )
        ->orWhere('email', 'like', "%{$text['character_search']}%");;
        return $query;
    }
}
