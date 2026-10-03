@extends('layouts.app')

@section('title', 'Multiplayer Chess Lobby — Create or Join Game | CHess')
@section('meta_description', 'Create a private multiplayer chess room or join a game with friends using a Game ID. Play real-time online chess matches with customizable time controls.')
@section('meta_keywords', 'multiplayer chess, play chess with friends, online chess multiplayer, chess game room, chess match online')

@section('content')
<div class="mx-auto max-w-xl py-8">
    <h1 class="text-3xl font-bold tracking-tight">Find an Opponent</h1>
    <p class="mt-1 text-zinc-600">Create a room and share the code, or join a friend's game.</p>

    @if (isset($activeWaitingGame) && $activeWaitingGame)
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active Waiting Room
                    </span>
                    <p class="mt-1 text-sm font-medium text-zinc-900">
                        Room code: <span class="font-mono font-bold text-emerald-800">{{ $activeWaitingGame->code }}</span> ({{ $activeWaitingGame->time_control }})
                    </p>
                    <p class="text-xs text-zinc-500">Waiting for an opponent to join.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('games.show', $activeWaitingGame->code) }}" class="rounded-xl bg-emerald-600 px-3.5 py-2 text-center text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        Rejoin Room
                    </a>
                    <form method="POST" action="{{ route('games.cancel', $activeWaitingGame->code) }}">
                        @csrf
                        <button class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

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

        <h2 class="font-bold">2. Join with a Game ID or Link</h2>
        <form method="POST" action="{{ route('join.post') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label for="code" class="text-sm font-medium text-zinc-700">Game ID or Invite Link</label>
                <input id="code" name="code" required value="{{ old('code') }}" placeholder="e.g. ABC123 or paste link"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-center font-mono text-base uppercase tracking-wider outline-none transition focus:border-emerald-500">
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
