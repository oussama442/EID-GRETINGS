@php
    $settings = \App\Models\Setting::current();
    $brandName = $settings->company_name ?: config('app.name');
    $logoUrl = $settings->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->logo) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName }} - {{ __('Premium car rental') }}</title>
    <meta name="description" content="{{ __('Browse available cars, check rental dates, and request a booking online.') }}">
    @if($settings->favicon)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased selection:bg-black selection:text-white">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:bg-black focus:px-4 focus:py-3 focus:text-white">{{ __('Skip to content') }}</a>
    <nav class="fixed w-full z-50 bg-white/85 backdrop-blur-xl border-b border-slate-200/70 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-9 w-auto">
                    @else
                        <div class="w-8 h-8 bg-black rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    @endif
                    <span class="font-bold text-xl tracking-tight">{{ $brandName }}</span>
                </div>
                <div class="hidden md:flex items-center space-x-6 rtl:space-x-reverse">
                    <a href="{{ route('public.home') }}" class="{{ request()->routeIs('public.home') ? 'text-black' : 'text-gray-600' }} hover:text-black font-medium transition-colors">{{ __('Home') }}</a>
                    <a href="{{ route('public.fleet') }}" class="{{ request()->routeIs('public.fleet') || request()->routeIs('public.car') ? 'text-black' : 'text-gray-600' }} hover:text-black font-medium transition-colors">{{ __('Our fleet') }}</a>
                    <div class="relative group">
                        <button class="text-gray-600 hover:text-black font-medium transition-colors flex items-center gap-1 uppercase">
                            {{ app()->getLocale() }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div class="absolute right-0 mt-2 w-24 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all">
                            <a href="{{ route('lang.switch', 'fr') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black first:rounded-t-xl">FR</a>
                            <a href="{{ route('lang.switch', 'ar') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black">AR</a>
                            <a href="{{ route('lang.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black last:rounded-b-xl">EN</a>
                        </div>
                    </div>
                    <a href="{{ route('filament.admin.auth.login') }}" class="bg-black text-white px-5 py-2.5 rounded-lg font-medium hover:bg-gray-800 active:scale-[0.98] transition-all shadow-md">{{ __('Staff area') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <main id="content" class="pt-20 min-h-screen flex flex-col">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-100 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-7 w-auto">
                    @else
                        <div class="w-6 h-6 bg-black rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    @endif
                    <span class="font-bold text-lg">{{ $brandName }}</span>
                </div>
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} {{ $brandName }}. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
