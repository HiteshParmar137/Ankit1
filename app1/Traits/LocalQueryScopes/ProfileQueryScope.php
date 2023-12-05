<?php

namespace App\Traits\LocalQueryScopes;

use Illuminate\Database\Eloquent\Builder;

trait ProfileQueryScope {
    public function scopeSearchByColumns(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->orWhere('profile_code', 'like', "%{$search}%");
            $query->orWhere('description', 'like', "%{$search}%");
            $query->orWhere('default_hours', 'like', "%{$search}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        $search = $filters['search'] ?? "";
        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "id";

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByColumns($search);
        });

        $query->orderBy($sortColumn, $sortType);
        return $query;
    }
}