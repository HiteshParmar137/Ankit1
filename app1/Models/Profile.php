<?php

namespace App\Models;

use App\Traits\LocalQueryScopes\ProfileQueryScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Profile extends Model
{
    use HasFactory, SoftDeletes, ProfileQueryScope;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Get the profile's users.
     */
    protected function users(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Str::isJson($value) ? json_decode($value) : null,
        );
    }
}
