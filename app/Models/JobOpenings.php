<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOpenings extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->where('name', 'LIKE', "%{$text['character_search']}%")
            ->orWhere(
                'number_of_position',
                'like',
                "%{$text['character_search']}%"
            )
            ->orWhere('description', 'like', "%{$text['character_search']}%");
        return $query;
    }
}