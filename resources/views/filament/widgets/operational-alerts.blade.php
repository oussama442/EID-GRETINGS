<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Operational alerts') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Overdue returns, expiring documents, and upcoming service work that need attention.') }}
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 dark:border-danger-900 dark:bg-danger-950/30">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-danger-700 dark:text-danger-300">{{ __('Overdue returns') }}</h3>
                    <span class="rounded-full bg-danger-100 px-2 py-0.5 text-xs font-semibold text-danger-700 dark:bg-danger-900 dark:text-danger-200">{{ $overdueReturns->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($overdueReturns as $booking)
                        <div class="text-sm">
                            <div class="font-medium">{{ $booking->reference_number }} · {{ $booking->car?->plate_number ?: __('No car') }}</div>
                            <div class="text-gray-500">{{ $booking->client?->full_name ?: __('No client') }} · {{ optional($booking->return_datetime_planned)->format('Y-m-d H:i') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('No alerts') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-900 dark:bg-warning-950/30">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-warning-700 dark:text-warning-300">{{ __('Insurance expiry') }}</h3>
                    <span class="rounded-full bg-warning-100 px-2 py-0.5 text-xs font-semibold text-warning-700 dark:bg-warning-900 dark:text-warning-200">{{ $insuranceExpiring->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($insuranceExpiring as $car)
                        <div class="text-sm">
                            <div class="font-medium">{{ $car->brand }} {{ $car->model }} · {{ $car->plate_number }}</div>
                            <div class="text-gray-500">{{ optional($car->insurance_expiry)->format('Y-m-d') }} · {{ $car->branch?->name }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('No alerts') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-900 dark:bg-warning-950/30">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-warning-700 dark:text-warning-300">{{ __('Registration expiry') }}</h3>
                    <span class="rounded-full bg-warning-100 px-2 py-0.5 text-xs font-semibold text-warning-700 dark:bg-warning-900 dark:text-warning-200">{{ $registrationExpiring->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($registrationExpiring as $car)
                        <div class="text-sm">
                            <div class="font-medium">{{ $car->brand }} {{ $car->model }} · {{ $car->plate_number }}</div>
                            <div class="text-gray-500">{{ optional($car->registration_expiry)->format('Y-m-d') }} · {{ $car->branch?->name }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('No alerts') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-info-200 bg-info-50 p-4 dark:border-info-900 dark:bg-info-950/30">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-info-700 dark:text-info-300">{{ __('Service due') }}</h3>
                    <span class="rounded-full bg-info-100 px-2 py-0.5 text-xs font-semibold text-info-700 dark:bg-info-900 dark:text-info-200">{{ $serviceDue->count() }}</span>
                </div>
                <div class="space-y-3">
                    @forelse($serviceDue as $car)
                        <div class="text-sm">
                            <div class="font-medium">{{ $car->brand }} {{ $car->model }} · {{ $car->plate_number }}</div>
                            <div class="text-gray-500">{{ optional($car->next_service_due)->format('Y-m-d') }} · {{ number_format($car->mileage) }} km</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('No alerts') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
