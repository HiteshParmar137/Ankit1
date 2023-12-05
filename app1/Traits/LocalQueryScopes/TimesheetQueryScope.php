<?php

namespace App\Traits\LocalQueryScopes;

use App\Helper\Helpers;
use Illuminate\Database\Eloquent\Builder;

trait TimesheetQueryScope {
    public function scopeOnlyForThisUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearchByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate);
    }

    public function scopeSearchByColumns(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        [$currentWeekStartDate, $currentWeekEndDate] = Helpers::getStartAndEndDatesOfCurrentWeek();

        $search = $filters['search'] ?? "";

        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "id";

        $startDate = $filters['start_date'] ?? $currentWeekStartDate;
        $endDate = $filters['end_date'] ?? $currentWeekEndDate;

        $query->when(!empty($startDate) && !empty($endDate), function ($query) use ($startDate, $endDate) {
            $query->searchByDateRange($startDate, $endDate);
        });

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByColumns($search);
        });

        $query->orderBy($sortColumn, $sortType);
        return $query;
    }

    public function scopeFilterHistory(Builder $query, array $filters = [])
    {
        $search = $filters['search'] ?? "";
        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "start_date";

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByColumns($search);
        });

        $query->orderBy($sortColumn, $sortType);
        return $query;
    }
}