<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pickup = '2026-07-02 08:00:00';
$return = '2026-07-05 18:00:00';
$value = 5;

$conflict = \App\Models\Booking::where('car_id', $value)
    ->whereIn('status', ['reserved', 'active'])
    ->where(function($query) use ($pickup, $return) {
        $query->where(function ($q) use ($pickup) {
            $q->whereNull('return_datetime_planned')
              ->orWhere('return_datetime_planned', '>', $pickup);
        });
        if ($return) {
            $query->where('pickup_datetime', '<', $return);
        }
    })->exists();

echo "Conflict exists: " . ($conflict ? "YES" : "NO") . "\n";
