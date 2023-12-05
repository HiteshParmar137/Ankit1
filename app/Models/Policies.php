<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policies extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('title', 'LIKE', "%{$text['character_search']}%");
        return $query;
    }
}
