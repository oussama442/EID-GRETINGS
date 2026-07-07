<x-filament-panels::page>
    @php
        $rows = $this->getRows();
        $summary = $this->getSummary();
    @endphp

    <div class="space-y-6">
        <form method="GET" action="{{ \App\Filament\Pages\RevenueReports::getUrl() }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="grid gap-4 md:grid-cols-4">
                <label class="space-y-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Group by') }}</span>
                    <select name="groupBy" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950">
                        @foreach($this->getGroups() as $value => $label)
                            <option value="{{ $value }}" @selected($groupBy === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Start date') }}</span>
                    <input type="date" name="startDate" value="{{ $startDate }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950">
                </label>
                <label class="space-y-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('End date') }}</span>
                    <input type="date" name="endDate" value="{{ $endDate }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950">
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-primary-600 px-4 text-sm font-semibold text-white hover:bg-primary-500">
                        {{ __('Apply filters') }}
                    </button>
                    <a href="{{ $this->getExportUrl('csv') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        {{ __('CSV') }}
                    </a>
                    <a href="{{ $this->getExportUrl('pdf') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        {{ __('PDF') }}
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500">{{ __('Groups') }}</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($summary['groups']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500">{{ __('Bookings') }}</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($summary['bookings']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500">{{ __('Payments') }}</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($summary['payments']) }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="text-sm text-gray-500">{{ __('Collected revenue') }}</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($summary['revenue'], 0) }} DZD</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-4 py-3 text-sm text-gray-500 dark:border-gray-700">
                {{ __('Date range') }}: {{ $this->getDateRangeLabel() }}
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-left dark:bg-gray-950">
                        <tr>
                            <th class="px-4 py-3 font-semibold">{{ __('Name') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('Details') }}</th>
                            <th class="px-4 py-3 text-right font-semibold">{{ __('Bookings') }}</th>
                            <th class="px-4 py-3 text-right font-semibold">{{ __('Payments') }}</th>
                            <th class="px-4 py-3 text-right font-semibold">{{ __('Collected revenue') }}</th>
                            <th class="px-4 py-3 text-right font-semibold">{{ __('Average payment') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($rows as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $row['label'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $row['secondary'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['bookings_count']) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['payments_count']) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($row['collected_revenue'], 0) }} DZD</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['average_payment'], 0) }} DZD</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">{{ __('No revenue found for this period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
