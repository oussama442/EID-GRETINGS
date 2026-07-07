<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Car;
use App\Support\BranchAccess;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationalAlerts extends Widget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.operational-alerts';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'overdueReturns' => $this->overdueReturns(),
            'insuranceExpiring' => $this->carsDueBy('insurance_expiry', 30),
            'registrationExpiring' => $this->carsDueBy('registration_expiry', 30),
            'serviceDue' => $this->carsDueBy('next_service_due', 30),
        ];
    }

    private function overdueReturns(): Collection
    {
        return BranchAccess::scope(Booking::with(['client', 'car', 'branch']))
            ->when($this->branchId(), fn (Builder $query, int|string $branchId): Builder => $query->where('branch_id', $branchId))
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('return_datetime_planned')
            ->where('return_datetime_planned', '<', now())
            ->orderBy('return_datetime_planned')
            ->limit(8)
            ->get();
    }

    private function carsDueBy(string $column, int $days): Collection
    {
        return BranchAccess::scope(Car::with('branch'))
            ->when($this->branchId(), fn (Builder $query, int|string $branchId): Builder => $query->where('branch_id', $branchId))
            ->whereNotNull($column)
            ->whereDate($column, '<=', now()->addDays($days))
            ->orderBy($column)
            ->limit(8)
            ->get();
    }

    private function branchId(): int|string|null
    {
        return $this->filters['branchId'] ?? null;
    }
}
