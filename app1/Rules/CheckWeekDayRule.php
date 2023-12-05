<?php

namespace App\Rules;

use App\Enums\Days;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class CheckWeekDayRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $day;
    public function __construct($day)
    {
        $this->day = $day;
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
        return Carbon::createFromFormat('Y-m-d', $value)->format('l') === $this->day;

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return ($this->day === Days::MONDAY ? 'Start' : 'End') . ' date day must be ' . Str::lower($this->day);
    }
}
