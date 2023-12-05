<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    const SUPER_ADMIN_ROLE_SLUG = 'super-admin';
    const HUMAN_RESOURCE_EXECUTIVE_ROLE_SLUG = 'human-resource-executive';
    const EMPLOYEE_ROLE_SLUG = 'employee';

    const SUPER_ADMIN_ROLE = 'Super Admin';
    const HUMAN_RESOURCE_EXECUTIVE_ROLE = 'Human Resource Executive';
    const EMPLOYEE_ROLE = 'Employee';
    const ACTIVE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password'];

    protected $appends = ['full_name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    public function isAdmin()
    {   
        return $this->hasRole(self::SUPER_ADMIN_ROLE);
    }

    public function isHr()
    {
        return $this->hasRole(self::HUMAN_RESOURCE_EXECUTIVE_ROLE);
    }

    public function isEmployee()
    {
        return $this->hasRole(self::EMPLOYEE_ROLE);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . " " . $this->last_name;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::ACTIVE)->orderBy('first_name');
    }
}