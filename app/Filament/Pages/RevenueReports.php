<?php

namespace App\Filament\Pages;

use App\Support\RevenueReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RevenueReports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.revenue-reports';

    public string $groupBy = 'car';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->groupBy = RevenueReport::normalizeGroup(request('groupBy', 'car'));
        $this->startDate = request('startDate', now()->startOfMonth()->toDateString());
        $this->endDate = request('endDate', now()->endOfMonth()->toDateString());
    }

    public static function getNavigationLabel(): string
    {
        return __('Revenue reports');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Revenue reports');
    }

    public function getRows(): Collection
    {
        return RevenueReport::rows($this->groupBy, $this->startDate, $this->endDate);
    }

    public function getSummary(): array
    {
        return RevenueReport::summary($this->groupBy, $this->startDate, $this->endDate);
    }

    public function getGroups(): array
    {
        return RevenueReport::groups();
    }

    public function getExportUrl(string $format): string
    {
        return route("admin.revenue-reports.export.{$format}", [
            'groupBy' => $this->groupBy,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function getDateRangeLabel(): string
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->format('Y-m-d') : __('Any date');
        $end = $this->endDate ? Carbon::parse($this->endDate)->format('Y-m-d') : __('Any date');

        return "{$start} - {$end}";
    }
}
