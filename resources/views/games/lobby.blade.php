@extends('layouts.app')

@section('title', 'Find an Opponent — CHess')

@section('content')
<div class="mx-auto max-w-xl py-8">
    <h1 class="text-3xl font-bold tracking-tight">Find an Opponent</h1>
    <p class="mt-1 text-zinc-600">Create a room and share the code, or join a friend's game.</p>

    <div class="mt-8 rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        <h2 class="font-bold">1. Create a Private Game</h2>
        <form method="POST" action="{{ route('games.create') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="mode" value="multi">
            <div>
                <label for="time_control" class="text-sm font-medium text-zinc-700">Time control</label>
                <select id="time_control" name="time_control"
                        class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500">
                    @foreach ($timeControls as $tc)
                        <option value="{{ $tc['id'] }}">{{ $tc['id'] }} — {{ $tc['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Create Game Room
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-zinc-300">
            <span class="h-px flex-1 bg-zinc-100"></span>OR<span class="h-px flex-1 bg-zinc-100"></span>
        </div>

        <h2 class="font-bold">2. Join with a Game ID</h2>
        <form method="POST" action="{{ route('join.post') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label for="code" class="text-sm font-medium text-zinc-700">Game ID</label>
                <input id="code" name="code" required maxlength="8" placeholder="e.g. ABC123"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-center font-mono text-lg uppercase tracking-widest outline-none transition focus:border-emerald-500">
                @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="w-full rounded-xl border border-emerald-600 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                Join Game
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-zinc-500">
        Want to practice first? <a href="{{ route('bot.setup') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Play vs Bot →</a>
    </p>
</div>
@endsection
