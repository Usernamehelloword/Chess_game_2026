@extends('layouts.app')

@section('title', 'Join Online Chess Game — Play with Friends | CHess')
@section('meta_description', 'Join an online chess game with your friends using your unique Game ID room code. Instant multiplayer chess in your browser.')
@section('meta_keywords', 'join chess game, play chess with friends, online chess room, multiplayer chess')

@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="rounded-3xl border border-zinc-100 bg-white p-8 text-center shadow-sm">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-3xl">🔑</span>
        <h1 class="mt-4 text-2xl font-bold">Join a Game</h1>
        <p class="mt-1 text-sm text-zinc-500">Enter the Game ID or paste the invite link your friend shared with you.</p>

        <form method="POST" action="{{ route('join.post') }}" class="mt-8 space-y-4">
            @csrf
            <input id="code" name="code" required autofocus placeholder="e.g. ABC123 or paste link"
                   value="{{ request('code', old('code')) }}"
                   class="w-full rounded-xl border border-zinc-200 px-4 py-3 text-center font-mono text-xl uppercase tracking-wider outline-none transition focus:border-emerald-500">
            @error('code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Join Game
            </button>
        </form>
    </div>
</div>
@endsection
