<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Revenue reports') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        .summary { margin: 18px 0; width: 100%; border-collapse: collapse; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px; }
        table.report { width: 100%; border-collapse: collapse; }
        .report th, .report td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        .report th { background: #f9fafb; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('Revenue reports') }}</h1>
    <div class="muted">{{ __('Group by') }}: {{ $groupLabel }} | {{ __('Date range') }}: {{ $startDate ?: __('Any date') }} - {{ $endDate ?: __('Any date') }}</div>

    <table class="summary">
        <tr>
            <td>{{ __('Groups') }}<br><strong>{{ number_format($summary['groups']) }}</strong></td>
            <td>{{ __('Bookings') }}<br><strong>{{ number_format($summary['bookings']) }}</strong></td>
            <td>{{ __('Payments') }}<br><strong>{{ number_format($summary['payments']) }}</strong></td>
            <td>{{ __('Collected revenue') }}<br><strong>{{ number_format($summary['revenue'], 0) }} DZD</strong></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Details') }}</th>
                <th class="right">{{ __('Bookings') }}</th>
                <th class="right">{{ __('Payments') }}</th>
                <th class="right">{{ __('Collected revenue') }}</th>
                <th class="right">{{ __('Average payment') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td>{{ $row['secondary'] ?: '-' }}</td>
                    <td class="right">{{ number_format($row['bookings_count']) }}</td>
                    <td class="right">{{ number_format($row['payments_count']) }}</td>
                    <td class="right">{{ number_format($row['collected_revenue'], 0) }} DZD</td>
                    <td class="right">{{ number_format($row['average_payment'], 0) }} DZD</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">{{ __('No revenue found for this period.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
