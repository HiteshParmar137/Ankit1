<?php

namespace App\Traits\LocalQueryScopes;

use Illuminate\Database\Eloquent\Builder;

trait ProjectQueryScope {
    public function scopeSearchByName(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        $status = $filters['status'] ?? "";
        $search = $filters['search'] ?? "";
        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "id";

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