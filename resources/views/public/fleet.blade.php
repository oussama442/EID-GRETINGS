@extends('layouts.public')

@section('content')
<div class="bg-slate-50 py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-10 grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
            <div>
                <a href="{{ route('public.home') }}" class="mb-5 inline-flex items-center text-sm font-semibold text-slate-500 transition hover:text-black">
                    <svg class="mr-2 h-4 w-4 rtl:ml-2 rtl:mr-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    {{ __('Back to home') }}
                </a>
                <h1 class="max-w-3xl text-4xl font-black leading-tight tracking-tight text-slate-950 md:text-6xl">{{ __('Choose a car that actually fits the trip') }}</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">{{ __('Filter by dates, budget, seats, and drivetrain details before sending a booking request.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ __('Results') }}</p>
                <p class="mt-1 text-3xl font-black tabular-nums text-slate-950">{{ $cars->total() }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('available vehicles match your search') }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('public.fleet') }}" class="mb-10 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="search" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Search') }}</label>
                    <input id="search" type="search" name="search" value="{{ request('search') }}" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="{{ __('Brand or model') }}">
                </div>
                <div>
                    <label for="pickup_datetime" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Pickup') }}</label>
                    <input id="pickup_datetime" type="datetime-local" name="pickup_datetime" value="{{ request('pickup_datetime') }}" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                    @error('pickup_datetime')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="return_datetime" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Return') }}</label>
                    <input id="return_datetime" type="datetime-local" name="return_datetime" value="{{ request('return_datetime') }}" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                    @error('return_datetime')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="category" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Category') }}</label>
                    <select id="category" name="category" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                        <option value="">{{ __('Any category') }}</option>
                        @foreach($filters['categories'] as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="transmission" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Transmission') }}</label>
                    <select id="transmission" name="transmission" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                        <option value="">{{ __('Any transmission') }}</option>
                        @foreach($filters['transmissions'] as $transmission)
                            <option value="{{ $transmission }}" @selected(request('transmission') === $transmission)>{{ __($transmission) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="fuel_type" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Fuel') }}</label>
                    <select id="fuel_type" name="fuel_type" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                        <option value="">{{ __('Any fuel') }}</option>
                        @foreach($filters['fuelTypes'] as $fuelType)
                            <option value="{{ $fuelType }}" @selected(request('fuel_type') === $fuelType)>{{ __($fuelType) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="seats" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Seats') }}</label>
                    <input id="seats" type="number" min="1" name="seats" value="{{ request('seats') }}" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="5+">
                </div>
                <div>
                    <label for="max_daily_rate" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Max daily rate') }}</label>
                    <input id="max_daily_rate" type="number" min="0" step="100" name="max_daily_rate" value="{{ request('max_daily_rate') }}" class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="12000">
                </div>
                <div class="flex items-end gap-3">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-black px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800 active:scale-[0.98]">
                        {{ __('Search fleet') }}
                    </button>
                    <a href="{{ route('public.fleet') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-black">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>

        @if($cars->count())
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach($cars as $car)
                    <article class="group flex min-h-full flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                        <div class="relative aspect-[16/10] overflow-hidden bg-slate-200">
                            <img src="{{ $car->photos->first() ? Storage::url($car->photos->first()->photo_path) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $car->brand }} {{ $car->model }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute left-4 top-4 rounded-md bg-white/90 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-900 backdrop-blur">
                                {{ $car->category }}
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-2xl font-black leading-tight text-slate-950">{{ $car->brand }} <span class="font-medium text-slate-700">{{ $car->model }}</span></h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ $car->year }} · {{ $car->color }}</p>
                                </div>
                                <div class="text-right rtl:text-left">
                                    <p class="text-2xl font-black tabular-nums text-slate-950">{{ number_format($car->daily_rate, 0) }}</p>
                                    <p class="text-xs font-semibold text-slate-500">DZD/{{ __('day') }}</p>
                                </div>
                            </div>
                            <div class="mt-5 grid grid-cols-3 gap-2 text-center text-sm">
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="block text-xs text-slate-500">{{ __('Seats') }}</span>
                                    <strong>{{ $car->seats }}</strong>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="block text-xs text-slate-500">{{ __('Gear') }}</span>
                                    <strong>{{ __($car->transmission) }}</strong>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <span class="block text-xs text-slate-500">{{ __('Fuel') }}</span>
                                    <strong>{{ __($car->fuel_type) }}</strong>
                                </div>
                            </div>
                            <a href="{{ route('public.car', array_merge(['id' => $car->id], request()->query())) }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-black px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800 active:scale-[0.98]">{{ __('Details and booking') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <h2 class="text-2xl font-black text-slate-950">{{ __('No matching cars right now') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-600">{{ __('Try changing the dates, budget, or vehicle category. If the trip is urgent, contact the rental desk directly.') }}</p>
                <a href="{{ route('public.fleet') }}" class="mt-6 inline-flex rounded-lg bg-black px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">{{ __('Clear filters') }}</a>
            </div>
        @endif

        <div class="mt-12 flex justify-center">
            {{ $cars->links() }}
        </div>
    </div>
</div>
@endsection
