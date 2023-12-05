<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTechnology extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function technologyData()
    {
        return $this->belongsTo(Technology::class, 'technology_id');
    }
}
