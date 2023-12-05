<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Freelancer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $guarded = ['id'];

    protected $appends = ['full_name'];

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'LIKE', "%".$text['character_search']."%")
            ->orWhere('email', 'like', "%{$text['character_search']}%")
            ->orWhere('contact_number', 'like', "%{$text['character_search']}%");
        return $query;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }

    public function technologies()
    {
        return $this->hasMany(FreelancerSkill::class, 'freelancer_id', 'id');
    }
}
