<?php

namespace App\Rules;

use App\Helper\Helpers;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class RestrictAdministratorRule implements Rule
{
    public $loggedInUserRole;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($loggedInUserRole) {
        $this->loggedInUserRole = $loggedInUserRole;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $administratorRoleGroups = Helpers::getAdministratorRoleGroups();

        if (
            in_array(
                $this->loggedInUserRole,
                $administratorRoleGroups
            )
        ) {
            if (!isset($value)) {
                return true;
            }

            $user = User::find($value);
            $role = Helpers::getLoginUserRole($user);
    
            return !in_array($role->name, $administratorRoleGroups);
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Administrator user can not add or update the my timesheet logs';
    }
}
