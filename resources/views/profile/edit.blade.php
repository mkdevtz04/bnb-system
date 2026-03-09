@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">
    {{-- Header --}}
    <div class="bg-[#003B95] text-white pt-10 pb-24 shadow-inner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-black italic tracking-tighter">Account Settings</h1>
            <p class="mt-2 text-blue-100 text-lg font-medium opacity-80 uppercase tracking-widest text-xs">CoastalCharmz Luxury Stays</p>
        </div>
    </div>

    {{-- Forms Container --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- Navigation Sidebar (Desktop) --}}
            <div class="md:col-span-1 space-y-4">
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 italic transition-transform hover:scale-[1.02]">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-[#003B95] text-2xl font-black">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="font-black text-gray-900 leading-tight">{{ Auth::user()->name }}</h2>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">{{ Auth::user()->role }} Account</p>
                        </div>
                    </div>
                    <nav class="space-y-2">
                        <a href="#profile-info" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-[#003B95] rounded-xl font-black italic">
                            <i class="fa-solid fa-user"></i>
                            General Info
                        </a>
                        <a href="#security" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl font-black italic transition">
                            <i class="fa-solid fa-shield-halved"></i>
                            Security
                        </a>
                        <a href="#danger-zone" class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-xl font-black italic transition">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Danger Zone
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="md:col-span-2 space-y-8">
                
                {{-- Profile Info --}}
                <div id="profile-info" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100 overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i class="fa-solid fa-user fa-8x text-blue-900"></i>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black text-gray-900 mb-8 italic border-b-4 border-blue-100 inline-block pb-1">Profile Information</h3>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Security --}}
                <div id="security" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                    <h3 class="text-2xl font-black text-gray-900 mb-8 italic border-b-4 border-blue-100 inline-block pb-1">Security & Password</h3>
                    @include('profile.partials.update-password-form')
                </div>

                {{-- Danger Zone --}}
                <div id="danger-zone" class="bg-white rounded-3xl shadow-2xl p-8 border-t-4 border-red-500">
                    <h3 class="text-2xl font-black text-red-600 mb-8 italic">Close Account</h3>
                    <p class="text-sm text-gray-500 mb-6 font-medium italic">Warning: This action is permanent. All your booking history and data will be permanently wiped from CoastalCharmz servers.</p>
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Smoothing Tailwind transitions */
    html {
        scroll-behavior: smooth;
    }
</style>
@endsection

