@extends('layouts.public')

@section('content')
@php
    $settings = \App\Models\Setting::current();
    $heroTitle = $settings->public_hero_title ?: __('Drive the future. Rent the exceptional.');
    $heroSubtitle = $settings->public_hero_subtitle ?: __('Discover a premium fleet built for comfort, style, and confident trips.');
@endphp

<section class="relative bg-black text-white py-32 overflow-hidden flex-1 flex flex-col justify-center">
    <div class="absolute inset-0 z-0 opacity-40">
        <img src="https://images.unsplash.com/photo-1503376760366-50e504c5dc5f?auto=format&fit=crop&w=2000&q=80" alt="{{ __('Hero background') }}" class="w-full h-full object-cover">
    </div>
    <div class="absolute inset-0 bg-gradient-to-r from-black to-transparent z-10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 w-full">
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight mb-6 max-w-2xl leading-tight">{{ $heroTitle }}</h1>
        <p class="text-xl md:text-2xl text-gray-300 mb-10 max-w-xl">{{ $heroSubtitle }}</p>
        <a href="{{ route('public.fleet') }}" class="inline-flex items-center gap-2 bg-white text-black px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
            {{ __('Explore the fleet') }}
            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
        </a>
    </div>
</section>

<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-gray-900">{{ __('Featured vehicles') }}</h2>
            <div class="w-24 h-1 bg-black mx-auto mt-6"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($featuredCars as $car)
                <div class="bg-gray-50 rounded-2xl overflow-hidden group hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col">
                    <div class="relative h-64 overflow-hidden">
                        <img src="{{ $car->photos->first() ? Storage::url($car->photos->first()->photo_path) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $car->brand }} {{ $car->model }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                            {{ $car->category }}
                        </div>
                    </div>
                    <div class="p-8 flex-1 flex flex-col">
                        <h3 class="text-2xl font-bold mb-2">{{ $car->brand }} <span class="font-light">{{ $car->model }}</span></h3>
                        <p class="text-gray-500 mb-6 flex items-center gap-4 text-sm">
                            <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg> {{ __($car->transmission) }}</span>
                        </p>
                        <div class="mt-auto">
                            <div class="flex justify-between items-end mb-6">
                                <div>
                                    <span class="text-sm text-gray-500 block mb-1">{{ __('Starting from') }}</span>
                                    <span class="text-3xl font-bold">{{ number_format($car->daily_rate, 0) }} <span class="text-lg font-medium text-gray-500">DZD/{{ __('day') }}</span></span>
                                </div>
                            </div>
                            <a href="{{ route('public.car', $car->id) }}" class="block w-full bg-black text-white text-center py-3 rounded-xl font-medium hover:bg-gray-800 transition-colors">{{ __('Details and booking') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
