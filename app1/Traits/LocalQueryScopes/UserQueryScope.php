<?php

namespace App\Traits\LocalQueryScopes;

use App\Enums\UserTypes;
use Illuminate\Database\Eloquent\Builder;

trait UserQueryScope {
    public function scopeOnlyEmployees(Builder $query): Builder
    {
        return $query->whereHas('roles', function($query) {
            $query->whereNotIn(
                'name',
                [
                    UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value),
                    UserTypes::getFormattedCaseKey(UserTypes::HR_ADMIN->value)
                ]
            );
        });
    }

    public function scopeWithoutSuperAdmin(Builder $query): Builder
    {
        return $query->whereHas('roles', function($query) {
            $query->whereNot('name', UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value));
        });
    }

    public function scopeUsersNamedRole(Builder $query, string|null $role): Builder
    {

        return $query->when(isset($role), function ($query) use ($role) {
            $query->whereHas('roles', function($query) use ($role) {
                $query->where('name', $role);
            });
        }, function($query) {
            $query->whereHas('roles', function($query){
                $query->whereIn('name', [
                    UserTypes::getFormattedCaseKey(UserTypes::EMPLOYEE->value),
                    UserTypes::getFormattedCaseKey(UserTypes::HR_ADMIN->value)
                ]);
            });
        });
    }

    public function scopeSearchByNameAndEmail(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw("concat(first_name, ' ', last_name) like '%{$search}%' ")
                ->orWhereRaw("concat(last_name, ' ', first_name) like '%{$search}%'")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeOrderByQuery(Builder $query, string $sortType = 'desc', string $sortColumn = "id"): Builder
    {
        return $query->when($sortColumn == "full_name", function ($query) use ($sortType, $sortColumn) {
            $query->orderByRaw("concat(first_name,' ',last_name) " . $sortType . "");
        }, function ($query) use ($sortType, $sortColumn) {
            $query->orderBy($sortColumn, $sortType);
        });
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        $status = $filters['status'] ?? "";
        $canWorkInAws = $filters['can_work_in_aws'] ?? null;
        $search = $filters['search'] ?? "";
        $sortType = $filters['sort_type'] ?? "desc";
        $sortColumn = $filters['sort_column'] ?? "id";

        $query->when(!empty($status), function ($query) use ($status) {
            $query->where('users.status', $status);
        });

        $query->when(!is_null($canWorkInAws), function ($query) use ($canWorkInAws) {
            $query->where('users.can_work_in_aws', $canWorkInAws);
        });

        $query->when(!empty($search), function ($query) use ($search) {
            $query->searchByNameAndEmail($search);
        });

        $query->orderByQuery($sortType, $sortColumn);
        return $query;
    }
}