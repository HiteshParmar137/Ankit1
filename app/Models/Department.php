<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function ScopeGetTextSearch($query, $text)
    {
        $query
            ->where('department_name', 'LIKE', "%{$text['character_search']}%")
            ->orWhere('slug', 'like', "%{$text['character_search']}%");
        return $query;
    }
}