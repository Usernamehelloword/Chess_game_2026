@extends('layouts.app')

@section('title', 'Play Chess Against Computer — Free Online Chess Bot | CHess')
@section('meta_description', 'Play chess against computer online for free. Choose bot difficulty levels from beginner to expert, practice tactical moves, and play a free chess game against computer.')
@section('meta_keywords', 'play chess against computer, chess vs computer, chess bot, free chess game against computer, play chess against computer online, single player chess')
@section('page', 'bot-setup')

@section('content')
<div class="mx-auto max-w-xl py-8">
    <div class="text-center sm:text-left">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
            🤖 Single Player Chess
        </span>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900">Play Chess Against Computer</h1>
        <p class="mt-1 text-zinc-600">Practice tactics and openings against our chess bot. Pick your strength, time control, and side.</p>
    </div>

    <form method="POST" action="{{ route('games.create') }}" class="mt-8 space-y-6 rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        @csrf
        <input type="hidden" name="mode" value="bot">

        <fieldset>
            <legend class="text-sm font-semibold text-zinc-700">Select Bot Difficulty</legend>
            <p class="text-xs text-zinc-500 mb-3">From friendly beginner to sharp tactical computer engine.</p>
            <div class="grid grid-cols-2 gap-3">
                @foreach ($difficulties as $i => $difficulty)
                <label class="cursor-pointer">
                    <input type="radio" name="difficulty" value="{{ $difficulty }}" class="peer sr-only" {{ $i === 0 ? 'checked' : '' }}>
                    <div class="rounded-2xl border border-zinc-200 p-4 text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-emerald-100 hover:border-emerald-300">
                        <div class="text-2xl">{{ ['🌱', '⚖️', '⚔️', '🔥'][$i] }}</div>
                        <div class="mt-1 text-sm font-semibold capitalize">{{ $difficulty }}</div>
                        <div class="text-xs text-zinc-500">{{ ['Chess for beginners', 'Balanced & friendly', 'Sharp tactical plays', 'Master calculation'][$i] }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </fieldset>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="time_control" class="text-sm font-semibold text-zinc-700">Time control</label>
                <select id="time_control" name="time_control"
                        class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500">
                    @foreach ($timeControls as $tc)
                        <option value="{{ $tc['id'] }}">{{ $tc['id'] }} — {{ $tc['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="color" class="text-sm font-semibold text-zinc-700">Play as</label>
                <select id="color" name="color"
                        class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500">
                    <option value="white">♔ White</option>
                    <option value="black">♚ Black</option>
                    <option value="random" selected>🎲 Random</option>
                </select>
            </div>
        </div>

        <button class="w-full rounded-xl bg-zinc-900 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">
            Start Game vs Bot
        </button>
    </form>
</div>
@endsection
