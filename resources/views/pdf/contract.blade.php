@php
    $settings = \App\Models\Setting::current();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Rental contract') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #111827; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        h1 { font-size: 22px; margin-bottom: 8px; }
        h3 { font-size: 15px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #d1d5db; text-align: left; }
        .muted { color: #6b7280; }
        .signature td { border: none; height: 80px; text-align: center; vertical-align: bottom; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Rental contract') }}</h1>
        <div class="muted">{{ $settings->company_name }}</div>
        <h3>{{ __('Reference') }}: {{ $booking->reference_number }}</h3>
    </div>

    <div class="section">
        <h3>{{ __('Customer') }}</h3>
        <p><strong>{{ __('Full name') }}:</strong> {{ $booking->client?->full_name }}</p>
        <p><strong>{{ __('Phone') }}:</strong> {{ $booking->client?->phone }}</p>
        <p><strong>{{ __('Driver license number') }}:</strong> {{ $booking->client?->driver_license_number }}</p>
    </div>

    <div class="section">
        <h3>{{ __('Vehicle') }}</h3>
        <p><strong>{{ __('Car') }}:</strong> {{ $booking->car?->brand }} {{ $booking->car?->model }}</p>
        <p><strong>{{ __('Plate number') }}:</strong> {{ $booking->car?->plate_number }}</p>
    </div>

    <div class="section">
        <h3>{{ __('Rental details') }}</h3>
        <table>
            <tr>
                <th>{{ __('Pickup date and time') }}</th>
                <td>{{ optional($booking->pickup_datetime)->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <th>{{ __('Planned return date and time') }}</th>
                <td>{{ optional($booking->return_datetime_planned)->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <th>{{ __('Daily rate') }}</th>
                <td>{{ number_format($booking->daily_rate_agreed, 2) }} {{ $settings->currency }}</td>
            </tr>
            <tr>
                <th>{{ __('Total amount') }}</th>
                <td>{{ number_format($booking->total_amount, 2) }} {{ $settings->currency }}</td>
            </tr>
            <tr>
                <th>{{ __('Deposit') }}</th>
                <td>{{ number_format($booking->deposit_amount, 2) }} {{ $settings->currency }}</td>
            </tr>
        </table>
    </div>

    @if($settings->contract_terms_template)
        <div class="section">
            <h3>{{ __('Terms') }}</h3>
            <p>{!! nl2br(e($settings->contract_terms_template)) !!}</p>
        </div>
    @endif

    <div class="section" style="margin-top: 50px;">
        <table class="signature">
            <tr>
                <td><strong>{{ __('Customer signature') }}</strong></td>
                <td><strong>{{ __('Agency signature') }}</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
