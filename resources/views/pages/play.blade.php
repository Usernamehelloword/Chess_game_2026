@extends('layouts.app')

@section('title', 'Choose a Game Mode — CHess')
@section('page', 'play')

@section('content')
<div class="mx-auto max-w-3xl py-8">
    <h1 class="text-center text-3xl font-bold tracking-tight">Choose your battle</h1>
    <p class="mt-2 text-center text-zinc-600">Two ways to play. Pick your opponent.</p>

    <div class="mt-10 grid gap-6 sm:grid-cols-2">
        <div class="group rounded-3xl border border-zinc-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-3xl transition group-hover:bg-emerald-100">♟️</div>
            <h2 class="mt-4 text-xl font-bold">Player vs Player</h2>
            <p class="mt-2 text-sm text-zinc-600">Challenge another player and compete in a real-time chess match.</p>
            <a href="{{ route('lobby') }}" class="mt-6 block rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition group-hover:bg-emerald-700 hover:bg-emerald-700">
                Play 1 vs 1
            </a>
        </div>

        <div class="group rounded-3xl border border-zinc-100 bg-white p-8 text-center shadow-sm transition hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-3xl transition group-hover:bg-amber-100">🤖</div>
            <h2 class="mt-4 text-xl font-bold">Player vs Bot</h2>
            <p class="mt-2 text-sm text-zinc-600">Practice your chess skills against an intelligent computer opponent.</p>
            <a href="{{ route('bot.setup') }}" class="mt-6 block rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                Play vs Bot
            </a>
        </div>
    </div>
</div>
@endsection
