<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait PrepareEnumDataMethods
{
    /**
     * @return mixed[]
     */
    public static function formattedForSelection(bool $nameInTitleCase = true): array
    {
        return collect(self::cases())->map(fn ($type): array => [
            'id' => $type->value,
            'name' => $nameInTitleCase ? Str::title(Str::replace('_', ' ', $type->name)) : $type->name,
        ])->toArray();
    }

    /**
     * @return mixed[]
     */
    public static function getList(): array
    {
        return collect(self::cases())->map(fn ($type): array => [
            'id' => $type->value,
            'value' => $type->value,
            'key' => Str::lower(Str::replace('_', '-', $type->name)),
            'name' => Str::title(Str::replace('_', ' ', $type->name)),
            'actual_enum_key' => $type->name
        ])->toArray();
    }

    public static function getValues(): string
    {
        return collect(self::cases())->pluck('value')->implode(',');
    }

    public static function getNames(): string
    {
        return str::lower(collect(self::cases())->pluck('name')->implode(','));
    }

    public static function getCasesValue(): Collection
    {
        return collect(self::cases())->pluck('value');
    }

    public static function getValueByCaseName(string $name): mixed
    {
        foreach (self::cases() as $type) {
            if ($type->name === $name) {
                return $type->value;
            }

            if (Str::upper(Str::replace(' ', '_', $name)) === $type->name) {
                return $type->value;
            }
        }

        return null;
    }

    public static function getFormattedCaseName(int|string $key): string
    {
        /** @var self $case */
        $case = self::tryFrom($key);

        return Str::title(Str::replace('_', ' ', $case->name));
    }

    public static function getFormattedCaseKey(int|string $key): string
    {
        /** @var self $case */
        $case = self::tryFrom($key);

        return Str::lower(Str::replace('_', '-', $case->name));
    }

    public static function getCaseNameByValue(int|string $id): string
    {
        /** @var self $case */
        $case = self::tryFrom($id);

        return $case->name;
    }

    /**
     * @return mixed[]
     */
    public static function getMatchingCases(string $name): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $caseName = Str::replace('_', ' ', strtolower($case->name));

            if (Str::contains($caseName, strtolower($name))) {
                $cases[] = $case->value;
            }
        }

        return $cases;
    }

    /**
     * @return mixed[]
     */
    public static function getCaseNames(string $format = ','): string
    {
        $collection = [];

        $values = collect(self::cases())->pluck('value');

        foreach ($values as $value) {
            $collection[] = self::getFormattedCaseKey($value);
        }

        return collect($collection)->implode($format);
    }
}
