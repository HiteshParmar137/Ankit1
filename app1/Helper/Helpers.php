<?php

namespace App\Helper;

use Carbon\Carbon;
use App\Enums\Days;
use App\Models\User;
use App\Enums\UserTypes;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class Helpers
{
    static public function getLoginUser(string $guard = 'api'): object
    {
        return auth()->guard($guard)->user();
    }

    static public function getLoginUserRole($user): ?object
    {
        return $user->roles->first() ?? null;
    }

    static public function getUserBasedOnLoggedInUserRole(int|null $requestUserId)
    {
        $user = Helpers::getLoginUser();

        $userId = $user->id;

        $administratorRoleGroups = Helpers::getAdministratorRoleGroups();

        if (
            isset($requestUserId) &&
            in_array(Helpers::getLoginUserRole($user)->name, $administratorRoleGroups)
        ) {
            $userId = $requestUserId;
            $user = User::find($userId);
        }

        return $user;
    }

    static public function getAdministratorRoleGroups(): mixed
    {
        return [
            UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value),
        ];
    }

    static public function getRolesWithoutSuperAdmin(): mixed
    {
        return [
            UserTypes::getFormattedCaseKey(UserTypes::EMPLOYEE->value),
            UserTypes::getFormattedCaseKey(UserTypes::HR_ADMIN->value),
        ];
    }

    static public function roundValue(float|string $value, int $roundWith = 2): mixed
    {
        return round($value, $roundWith);
    }

    static public function getStartAndEndDatesOfCurrentWeek(): array
    {
        $now = Carbon::now();

        $weekStartDate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $weekEndDate = $now->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        return [$weekStartDate, $weekEndDate];
    }

    static public function getAllDatesBetweenTwoDates(string $startDate, string $endDate): Collection
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        $dates = [];

        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return collect($dates);
    }

    static public function formatDate(string $date, string $format = 'm-d-Y')
    {
        return Carbon::parse($date)->format($format);
    }

    static public function getWeekEndDays(): mixed
    {
        return [
            Days::SATURDAY->value,
            Days::SUNDAY->value
        ];
    }

    /**
     * Generate a random, secure password.
     *
     * @param int $length
     * @param bool $letters
     * @param bool $numbers
     * @param bool $symbols
     * @param bool $spaces
     * @return string
     * @throws \Exception
     */
    public static function generateStrongPassword(
        int $length = 32,
        bool $letters = true,
        bool $numbers = true,
        bool $symbols = true,
        bool $spaces = false)
    : string
    {
        return (new Collection)
            ->when($letters, fn ($c) => $c->merge([
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
                'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
                'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
                'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            ]))
            ->when($numbers, fn ($c) => $c->merge([
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            ]))
            ->when($symbols, fn ($c) => $c->merge([
                '~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '-',
                '_', '.', ',', '<', '>', '?', '/', '\\', '{', '}', '[',
                ']', '|', ':', ';',
            ]))
            ->when($spaces, fn ($c) => $c->merge([' ']))
            ->pipe(fn ($c) => Collection::times($length, fn () => $c[random_int(0, $c->count() - 1)]))
            ->implode('');
    }
}
