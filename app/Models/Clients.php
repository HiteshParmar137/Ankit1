<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clients extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function countryName(){
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function companyCountry(){
        return $this->belongsTo(Country::class, 'company_country', 'id');
    }

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('name', 'LIKE', "%{$text}%");

        return $query;
    }
}
