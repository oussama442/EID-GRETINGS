@extends('layouts.public')

@section('content')
<div class="flex-1 flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 text-center border border-gray-100 transform transition-all hover:scale-105">
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-8">
            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-extrabold text-gray-900 mb-4">{{ __('Request sent') }}</h2>
        <p class="text-gray-500 mb-10 leading-relaxed">
            {{ __('Your booking request was saved successfully. Our team will contact you soon to confirm the details.') }}
        </p>
        <a href="{{ route('public.home') }}" class="inline-flex items-center justify-center w-full bg-black text-white px-6 py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors shadow-md">
            <svg class="w-5 h-5 mr-2 rtl:ml-2 rtl:mr-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ __('Back to home') }}
        </a>
    </div>
</div>
@endsection
