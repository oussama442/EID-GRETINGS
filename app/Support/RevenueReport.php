<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RevenueReport
{
    public static function groups(): array
    {
        return [
            'car' => __('Car'),
            'branch' => __('Branch'),
            'agent' => __('Agent'),
            'customer' => __('Customer'),
        ];
    }

    public static function normalizeGroup(?string $groupBy): string
    {
        return array_key_exists($groupBy, self::groups()) ? $groupBy : 'car';
    }

    public static function rows(?string $groupBy, ?string $startDate, ?string $endDate): Collection
    {
        $groupBy = self::normalizeGroup($groupBy);
        $query = self::baseQuery($startDate, $endDate);

        self::applyGroup($query, $groupBy);

        return $query
            ->orderByDesc('collected_revenue')
            ->get()
            ->map(fn ($row): array => self::formatRow($row, $groupBy));
    }

    public static function summary(?string $groupBy, ?string $startDate, ?string $endDate): array
    {
        $rows = self::rows($groupBy, $startDate, $endDate);

        return [
            'groups' => $rows->count(),
            'bookings' => $rows->sum('bookings_count'),
            'payments' => $rows->sum('payments_count'),
            'revenue' => $rows->sum('collected_revenue'),
        ];
    }

    public static function filename(?string $groupBy, ?string $extension): string
    {
        return 'revenue-' . self::normalizeGroup($groupBy) . '-' . now()->format('Ymd-His') . '.' . $extension;
    }

    private static function baseQuery(?string $startDate, ?string $endDate): Builder
    {
        $query = DB::table('payments')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->selectRaw('COUNT(DISTINCT bookings.id) as bookings_count')
            ->selectRaw('COUNT(payments.id) as payments_count')
            ->selectRaw('SUM(payments.amount) as collected_revenue')
            ->selectRaw('AVG(payments.amount) as average_payment');

        if ($startDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $query->where(function (Builder $query) use ($start): void {
                $query
                    ->where('payments.paid_at', '>=', $start)
                    ->orWhere(function (Builder $query) use ($start): void {
                        $query
                            ->whereNull('payments.paid_at')
                            ->where('payments.created_at', '>=', $start);
                    });
            });
        }

        if ($endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
            $query->where(function (Builder $query) use ($end): void {
                $query
                    ->where('payments.paid_at', '<=', $end)
                    ->orWhere(function (Builder $query) use ($end): void {
                        $query
                            ->whereNull('payments.paid_at')
                            ->where('payments.created_at', '<=', $end);
                    });
            });
        }

        if (BranchAccess::isRestricted()) {
            $query->where('bookings.branch_id', BranchAccess::branchId());
        }

        return $query;
    }

    private static function applyGroup(Builder $query, string $groupBy): void
    {
        match ($groupBy) {
            'branch' => $query
                ->leftJoin('branches', 'bookings.branch_id', '=', 'branches.id')
                ->addSelect([
                    'branches.id as group_id',
                    'branches.name as branch_name',
                    'branches.city as branch_city',
                ])
                ->groupBy('branches.id', 'branches.name', 'branches.city'),
            'agent' => $query
                ->leftJoin('users', 'bookings.agent_id', '=', 'users.id')
                ->addSelect([
                    'users.id as group_id',
                    'users.name as agent_name',
                    'users.email as agent_email',
                ])
                ->groupBy('users.id', 'users.name', 'users.email'),
            'customer' => $query
                ->leftJoin('clients', 'bookings.client_id', '=', 'clients.id')
                ->addSelect([
                    'clients.id as group_id',
                    'clients.full_name as customer_name',
                    'clients.phone as customer_phone',
                    'clients.email as customer_email',
                ])
                ->groupBy('clients.id', 'clients.full_name', 'clients.phone', 'clients.email'),
            default => $query
                ->leftJoin('cars', 'bookings.car_id', '=', 'cars.id')
                ->addSelect([
                    'cars.id as group_id',
                    'cars.brand as car_brand',
                    'cars.model as car_model',
                    'cars.plate_number as car_plate',
                ])
                ->groupBy('cars.id', 'cars.brand', 'cars.model', 'cars.plate_number'),
        };
    }

    private static function formatRow(object $row, string $groupBy): array
    {
        $label = match ($groupBy) {
            'branch' => $row->branch_name ?: __('No branch'),
            'agent' => $row->agent_name ?: __('Unassigned agent'),
            'customer' => $row->customer_name ?: __('Unknown customer'),
            default => trim(($row->car_brand ?: '') . ' ' . ($row->car_model ?: '')) ?: __('Unknown car'),
        };

        $secondary = match ($groupBy) {
            'branch' => $row->branch_city,
            'agent' => $row->agent_email,
            'customer' => $row->customer_phone ?: $row->customer_email,
            default => $row->car_plate,
        };

        return [
            'group_id' => $row->group_id,
            'label' => $label,
            'secondary' => $secondary,
            'bookings_count' => (int) $row->bookings_count,
            'payments_count' => (int) $row->payments_count,
            'collected_revenue' => (float) $row->collected_revenue,
            'average_payment' => (float) $row->average_payment,
        ];
    }
}
