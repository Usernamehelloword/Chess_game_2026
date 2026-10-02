@extends('layouts.app')

@section('title', 'CHess — Play Chess Online')
@section('page', 'home')

@section('content')
<section class="grid items-center gap-10 py-8 lg:grid-cols-2 lg:py-16">
    <div class="fade-in">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
            ♟️ Free online chess
        </span>
        <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-zinc-900 sm:text-5xl lg:text-6xl">
            Play Chess.<br><span class="text-emerald-600">Think Ahead.</span>
        </h1>
        <p class="mt-4 max-w-md text-lg text-zinc-600">
            Challenge your friends or test your strategy against an intelligent chess bot.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ auth()->check() ? route('lobby') : route('login') }}"
               class="group flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-7 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                ♟️ Play 1 vs 1
                <span class="transition group-hover:translate-x-0.5">→</span>
            </a>
            <a href="{{ auth()->check() ? route('bot.setup') : route('login') }}"
               class="group flex items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-white px-7 py-4 text-base font-semibold text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-700">
                🤖 Play vs Bot
                <span class="transition group-hover:translate-x-0.5">→</span>
            </a>
        </div>

        <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 text-center">
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">Games</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $totalGames }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">Players</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $totalPlayers }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">vs Bot</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $botGames }}</dd>
            </div>
        </dl>
    </div>

    <div class="fade-in">
        <div class="board-frame mx-auto max-w-md lg:max-w-lg">
            <div id="hero-board"
                 data-fen="r1bq1rk1/pp2bppp/2n1pn2/3p4/2PP4/2N1PN2/PP2BPPP/R1BQ1RK1 w - - 0 9"
                 aria-label="Chessboard preview"></div>
        </div>
        <p class="mt-3 text-center text-xs text-zinc-400">A lively middlegame — every move matters.</p>
    </div>
</section>

<section class="mt-12 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">⚡</span>
        <h3 class="mt-2 font-semibold">Fast & Real-time</h3>
        <p class="mt-1 text-sm text-zinc-600">Moves sync instantly between players, with seven time controls from bullet to classical.</p>
    </div>
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">🧠</span>
        <h3 class="mt-2 font-semibold">Smart Bot</h3>
        <p class="mt-1 text-sm text-zinc-600">Four difficulty levels, from a friendly beginner to a punishing expert engine.</p>
    </div>
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">📈</span>
        <h3 class="mt-2 font-semibold">Ratings & Stats</h3>
        <p class="mt-1 text-sm text-zinc-600">Elo rating updates after every rated game. Climb the leaderboard.</p>
    </div>
</section>

@if ($topPlayers->isNotEmpty())
<section class="mt-12 rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold">Top Players</h2>
        <a href="{{ route('leaderboard') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View leaderboard →</a>
    </div>
    <ol class="mt-4 space-y-2">
        @foreach ($topPlayers as $i => $player)
        <li class="flex items-center gap-3 rounded-xl px-3 py-2 {{ $i === 0 ? 'bg-amber-50' : '' }}">
            <span class="w-6 text-center text-sm font-bold text-zinc-400">{{ ['🥇', '🥈', '🥉'][$i] ?? $i + 1 }}</span>
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">{{ $player->avatar }}</span>
            <a href="{{ route('profile.show', $player->id) }}" class="font-medium hover:text-emerald-600">{{ $player->name }}</a>
            <span class="ml-auto font-mono text-sm text-zinc-500">{{ $player->rating }}</span>
        </li>
        @endforeach
    </ol>
</section>
@endif
@endsection
