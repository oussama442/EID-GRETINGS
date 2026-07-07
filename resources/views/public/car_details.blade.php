@extends('layouts.public')

@section('content')
@php
    $settings = \App\Models\Setting::current();
    $mainPhoto = $car->photos->first();
    $whatsapp = $settings->whatsapp_number ? preg_replace('/\D+/', '', $settings->whatsapp_number) : null;
    $features = is_array($car->features) ? $car->features : [];
@endphp

<div class="bg-slate-50 py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('public.fleet', request()->query()) }}" class="mb-6 inline-flex items-center text-sm font-semibold text-slate-500 transition hover:text-black">
            <svg class="mr-2 h-4 w-4 rtl:ml-2 rtl:mr-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ __('Back to fleet') }}
        </a>

        <div class="grid gap-8 lg:grid-cols-[1.08fr_0.92fr]">
            <section class="space-y-5">
                <div class="overflow-hidden rounded-xl bg-slate-200 shadow-sm ring-1 ring-slate-200">
                    <img src="{{ $mainPhoto ? Storage::url($mainPhoto->photo_path) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1400&q=80' }}" alt="{{ $car->brand }} {{ $car->model }}" class="aspect-[16/10] w-full object-cover">
                </div>

                @if($car->photos->count() > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($car->photos->skip(1)->take(4) as $photo)
                            <img src="{{ Storage::url($photo->photo_path) }}" alt="{{ $car->brand }} {{ $car->model }}" class="aspect-[4/3] rounded-lg object-cover ring-1 ring-slate-200">
                        @endforeach
                    </div>
                @endif

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black text-slate-950">{{ __('Technical specifications') }}</h2>
                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Transmission') }}</span>
                            <strong class="mt-1 block text-slate-950">{{ __($car->transmission) }}</strong>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Fuel type') }}</span>
                            <strong class="mt-1 block text-slate-950">{{ __($car->fuel_type) }}</strong>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Seats') }}</span>
                            <strong class="mt-1 block text-slate-950">{{ $car->seats }}</strong>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Mileage') }}</span>
                            <strong class="mt-1 block text-slate-950 tabular-nums">{{ number_format($car->mileage) }} km</strong>
                        </div>
                    </div>

                    @if(count($features))
                        <div class="mt-6">
                            <h3 class="text-sm font-bold text-slate-700">{{ __('Included features') }}</h3>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($features as $feature)
                                    <span class="rounded-md bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-800">{{ $feature }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <aside class="space-y-5">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-black px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">{{ $car->category }}</span>
                        @if($car->branch)
                            <span class="rounded-md bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $car->branch->name }}</span>
                        @endif
                    </div>
                    <h1 class="mt-5 text-4xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl">{{ $car->brand }} <span class="font-medium text-slate-700">{{ $car->model }}</span></h1>
                    <p class="mt-3 text-slate-500">{{ $car->year }} · {{ $car->color }} · {{ $car->plate_number }}</p>

                    <div class="mt-7 grid grid-cols-3 gap-3">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Daily') }}</span>
                            <strong class="mt-1 block text-xl tabular-nums text-slate-950">{{ number_format($car->daily_rate, 0) }}</strong>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Weekly') }}</span>
                            <strong class="mt-1 block text-xl tabular-nums text-slate-950">{{ $car->weekly_rate ? number_format($car->weekly_rate, 0) : '-' }}</strong>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <span class="text-xs font-semibold text-slate-500">{{ __('Monthly') }}</span>
                            <strong class="mt-1 block text-xl tabular-nums text-slate-950">{{ $car->monthly_rate ? number_format($car->monthly_rate, 0) : '-' }}</strong>
                        </div>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-500">{{ $settings->currency ?: 'DZD' }}</p>

                    @if($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode(__('Hi, I want to book') . ' ' . $car->brand . ' ' . $car->model) }}" class="mt-6 inline-flex w-full items-center justify-center rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-400 hover:text-black">
                            {{ __('Ask on WhatsApp') }}
                        </a>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black text-slate-950">{{ __('Request booking') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Send the request now. The team confirms the car, price, and pickup details before final approval.') }}</p>

                    @if($errors->any())
                        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <p class="font-bold">{{ __('Please fix the booking details below.') }}</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('public.book', $car->id) }}" method="POST" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="full_name" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Full name') }}</label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="{{ __('Full name') }}">
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="client@example.com">
                            </div>
                            <div>
                                <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Phone') }}</label>
                                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black" placeholder="+213 555 55 55 55">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="pickup_datetime" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Pickup date') }}</label>
                                <input id="pickup_datetime" type="datetime-local" name="pickup_datetime" value="{{ old('pickup_datetime', request('pickup_datetime')) }}" required class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                            </div>
                            <div>
                                <label for="return_datetime" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Return date') }}</label>
                                <input id="return_datetime" type="datetime-local" name="return_datetime" value="{{ old('return_datetime', request('return_datetime')) }}" required class="w-full rounded-lg border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-black focus:bg-white focus:ring-black">
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-black px-5 py-4 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800 active:scale-[0.98]">
                            {{ __('Send booking request') }}
                        </button>
                        <p class="text-center text-xs text-slate-500">{{ __('No payment is taken before confirmation.') }}</p>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
