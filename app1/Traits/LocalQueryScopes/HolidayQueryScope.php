<?php

namespace App\Traits\LocalQueryScopes;

use Illuminate\Database\Eloquent\Builder;

trait HolidayQueryScope {
    public function scopeSearchByName(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->orWhere('name', 'like', "%{$search}%");
            $query->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        $status = $filters['status'] ?? "";
        $search = $filters['search'] ?? "";
        $sortType = $filters['sort_type'] ?? "asc";
        $sortColumn = $filters['sort_column'] ?? "date";

        $query->when(!empty($status), function ($query) use ($status) {
            $query->where('status', $status);
        });

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByName($search);
        });

        $query->orderBy($sortColumn, $sortType);
        return $query;
    }
}