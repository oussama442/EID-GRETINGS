<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BranchAccess
{
    public static function user(?User $user = null): ?User
    {
        return $user ?? Auth::user();
    }

    public static function isRestricted(?User $user = null): bool
    {
        $user = self::user($user);

        if (! $user || ! $user->branch_id) {
            return false;
        }

        return strtolower((string) $user->role) === 'agent';
    }

    public static function branchId(?User $user = null): ?int
    {
        return self::user($user)?->branch_id;
    }

    public static function scope(Builder $query, string $column = 'branch_id', ?User $user = null): Builder
    {
        if (self::isRestricted($user)) {
            $query->where($column, self::branchId($user));
        }

        return $query;
    }

    public static function canManageCompany(?User $user = null): bool
    {
        $user = self::user($user);

        if (! $user) {
            return false;
        }

        return in_array(strtolower((string) $user->role), ['super admin', 'manager'], true);
    }
}
