@extends('layouts.app')

@section('title', 'Play vs Bot — CHess')

@section('content')
<div class="mx-auto max-w-xl py-8">
    <h1 class="text-3xl font-bold tracking-tight">Play vs Bot</h1>
    <p class="mt-1 text-zinc-600">Choose the bot's strength, the clock, and your color.</p>

    <form method="POST" action="{{ route('games.create') }}" class="mt-8 space-y-6 rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        @csrf
        <input type="hidden" name="mode" value="bot">

        <fieldset>
            <legend class="text-sm font-semibold text-zinc-700">Difficulty</legend>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($difficulties as $i => $difficulty)
                <label class="cursor-pointer">
                    <input type="radio" name="difficulty" value="{{ $difficulty }}" class="peer sr-only" {{ $i === 0 ? 'checked' : '' }}>
                    <div class="rounded-2xl border border-zinc-200 p-4 text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-emerald-100 hover:border-emerald-300">
                        <div class="text-2xl">{{ ['🌱', '⚖️', '⚔️', '🔥'][$i] }}</div>
                        <div class="mt-1 text-sm font-semibold capitalize">{{ $difficulty }}</div>
                        <div class="text-xs text-zinc-500">{{ ['Learning the ropes', 'A fair fight', 'Plays sharp tactics', 'Very challenging'][$i] }}</div>
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

        <button class="w-full rounded-xl bg-zinc-900 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
            Start Game
        </button>
    </form>
</div>
@endsection
