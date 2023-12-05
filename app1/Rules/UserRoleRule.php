<?php

namespace App\Rules;

use App\Enums\UserTypes;
use Illuminate\Contracts\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserRoleRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $roleName;
    public function __construct($roleName)
    {
        $this->roleName = $roleName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        $validRoles = Role::where('name', $this->roleName)
            ->whereNot('name', UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value))
            ->pluck('name')
            ->toArray();

        return in_array($value, $validRoles);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Selected role is invalid.';
    }
}
