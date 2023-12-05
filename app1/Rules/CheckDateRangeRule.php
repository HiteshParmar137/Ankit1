<?php

namespace App\Rules;

use App\Enums\Days;
use App\Enums\Settings;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class CheckDateRangeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $startDate;
    public function __construct($startDate)
    {
        $this->startDate = $startDate;
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
        $diffInDays = Carbon::parse($this->startDate)->diffInDays($value);
        $diffInDays += 1;

        return $diffInDays === (int) Settings::TOTAL_WEEKDAYS->value;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Start date and end date day duration must be 7 days.';
    }
}
