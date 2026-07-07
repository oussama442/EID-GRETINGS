@php
    $settings = \App\Models\Setting::current();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Payment receipt') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; color: #111827; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Payment receipt') }}</h1>
        <div class="muted">{{ $settings->company_name }}</div>
        <h3>{{ __('Receipt number') }}: {{ $payment->receipt_number ?: $payment->id }}</h3>
        <h4>{{ __('Booking reference') }}: {{ $payment->booking?->reference_number }}</h4>
    </div>

    <div class="section">
        <table>
            <tr>
                <th>{{ __('Client') }}</th>
                <td>{{ $payment->booking?->client?->full_name }}</td>
            </tr>
            <tr>
                <th>{{ __('Paid at') }}</th>
                <td>{{ optional($payment->paid_at ?: $payment->created_at)->format('Y-m-d H:i') }}</td>
            </tr>
            <tr>
                <th>{{ __('Amount') }}</th>
                <td>{{ number_format($payment->amount, 2) }} {{ $settings->currency }}</td>
            </tr>
            <tr>
                <th>{{ __('Payment method') }}</th>
                <td>{{ __($payment->method) }}</td>
            </tr>
            <tr>
                <th>{{ __('Payment type') }}</th>
                <td>{{ __($payment->type) }}</td>
            </tr>
        </table>
    </div>

    @if($settings->receipt_footer)
        <div class="section">
            <p>{!! nl2br(e($settings->receipt_footer)) !!}</p>
        </div>
    @endif

    <div class="section" style="margin-top: 50px;">
        <p style="text-align: right;"><strong>{{ __('Agency signature') }}</strong></p>
    </div>
</body>
</html>
