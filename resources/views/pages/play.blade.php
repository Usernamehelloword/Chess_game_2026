@extends('layouts.app')

@section('title', 'Online Chess Game — Play Multiplayer or vs Computer | CHess')
@section('meta_description', 'Start an online chess game instantly. Choose between 1 vs 1 multiplayer chess online with friends or practice single-player chess against computer bots. Free online chess modes.')
@section('meta_keywords', 'online chess game, chess game online, multiplayer chess, play chess online with friends, online multiplayer chess game, chess match online, chess game modes')
@section('page', 'play')

@section('content')
<div class="mx-auto max-w-3xl py-8">
    <div class="text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
            ♟️ Online Chess Match Modes
        </span>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">Online Chess Game Modes</h1>
        <p class="mt-2 text-zinc-600">Choose how you want to play chess online. Challenge real players or practice against our computer engine.</p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2">
        <!-- Multiplayer Chess Mode -->
        <div class="group rounded-3xl border border-zinc-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-3xl transition group-hover:bg-emerald-100">♟️</div>
            <h2 class="mt-4 text-xl font-bold text-zinc-900">Multiplayer Chess (1 vs 1)</h2>
            <p class="mt-2 text-sm text-zinc-600">Play chess online with friends or players worldwide. Create a room, share the code, and compete in live online chess matches.</p>
            <a href="{{ route('lobby') }}" class="mt-6 block rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition group-hover:bg-emerald-700 hover:bg-emerald-700">
                Play Multiplayer Chess
            </a>
        </div>

        <!-- Computer Bot Mode -->
        <div class="group rounded-3xl border border-zinc-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-3xl transition group-hover:bg-amber-100">🤖</div>
            <h2 class="mt-4 text-xl font-bold text-zinc-900">Play Chess Against Computer</h2>
            <p class="mt-2 text-sm text-zinc-600">Sharpen your tactics and openings with our chess bot. Pick from 4 difficulty levels with customizable timers.</p>
            <a href="{{ route('bot.setup') }}" class="mt-6 block rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                Play vs Computer Bot
            </a>
        </div>
    </div>

    <!-- Quick Navigation Links for SEO & UX -->
    <div class="mt-12 rounded-2xl border border-zinc-100 bg-white p-6 text-center text-sm text-zinc-600 shadow-sm">
        <span>Need a refresher on moves? Check our <a href="{{ route('how-to-play') }}" class="font-medium text-emerald-600 underline hover:text-emerald-700">How to Play Chess</a> guide, or see who is winning on the <a href="{{ route('leaderboard') }}" class="font-medium text-emerald-600 underline hover:text-emerald-700">Online Chess Leaderboard</a>.</span>
    </div>
</div>
@endsection
