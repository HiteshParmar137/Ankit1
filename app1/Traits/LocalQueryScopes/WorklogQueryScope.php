<?php

namespace App\Traits\LocalQueryScopes;

use Illuminate\Database\Eloquent\Builder;

trait WorklogQueryScope {
    public function scopeOnlyForThisUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearchByDateRange(Builder $query, string $fromDate, string $toDate): Builder
    {
        return $query->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);
    }

    public function scopeSearchByProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeSearchByColumns(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->orWhere('description', 'like', "%{$search}%");
            $query->orWhere('worked_hours', 'like', "%{$search}%");
            $query->orWhere('projects.name', 'like', "%{$search}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        $search = $filters['search'] ?? "";
        $projectId = $filters['project_id'] ?? null;

        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "id";

        $query->when(!empty($projectId), function ($query) use ($projectId) {
            $query->searchByProject($projectId);
        });

        $query->when(!empty($fromDate) && !empty($toDate), function ($query) use ($fromDate, $toDate) {
            $query->searchByDateRange($fromDate, $toDate);
        });

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByColumns($search);
        });

        $query->orderBy($sortColumn, $sortType);
        return $query;
    }
}