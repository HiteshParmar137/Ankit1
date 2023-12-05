<?php

namespace App\Models;

use App\Traits\LocalQueryScopes\WorklogQueryScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worklog extends Model
{
    use HasFactory, SoftDeletes, WorklogQueryScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];
}
