@extends('layouts.app')

@section('title', 'Game '.$state['game']['code'].' — CHess')
@section('page', $state['game']['status'] === 'waiting' ? 'game-waiting' : 'game')
@section('body-data', 'data-code="'.$state['game']['code'].'"')

@section('content')
@php
    $gameConfig = [
        'code' => $state['game']['code'],
        'mode' => $state['game']['mode'],
        'myColor' => $myColor,
        'base' => $state['game']['base'],
        'inc' => $state['game']['inc'],
        'moveCount' => $state['move_count'],
        'finished' => $state['game']['status'] === 'finished',
        'winner' => $state['game']['winner'],
        'reason' => $state['game']['result_reason'],
        'drawOfferedBy' => $state['game']['draw_offered_by'],
        'moves' => array_map(fn ($m) => ['n' => $m['n'], 'san' => $m['san'], 'color' => $m['color'], 'uci' => $m['uci']], $state['moves']),
        'lastMove' => $state['last_move'],
    ];
@endphp

<script id="game-config" type="application/json">@json($gameConfig)</script>

<div class="mx-auto max-w-6xl">
    @if ($state['game']['status'] === 'waiting')
        <div class="pop-in mx-auto mb-6 max-w-xl rounded-3xl border border-emerald-200 bg-emerald-50 p-8 text-center">
            <span class="inline-block h-8 w-8 rounded-full border-4 border-emerald-200 border-t-emerald-600 spin-slow"></span>
            <h2 class="mt-3 text-lg font-bold text-zinc-900">Waiting for an opponent...</h2>
            <p class="mt-1 text-sm text-emerald-800">Share your Game ID or direct invite link with your friend. The game starts automatically when they join.</p>
            
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <div class="inline-flex items-center gap-3 rounded-2xl bg-white px-5 py-2.5 shadow-sm">
                    <span class="font-mono text-2xl font-bold tracking-[0.25em] text-emerald-700">{{ $state['game']['code'] }}</span>
                    <button data-copy="{{ $state['game']['code'] }}" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">Copy Code</button>
                </div>
                <button data-copy="{{ url('/games/'.$state['game']['code']) }}" class="inline-flex items-center gap-1.5 rounded-2xl border border-emerald-300 bg-white px-4 py-2.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                    🔗 Copy Invite Link
                </button>
            </div>

            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-emerald-700">
                <span>Time control: {{ $state['game']['time_control'] }}</span>
                <span>•</span>
                <form method="POST" action="{{ route('games.cancel', $state['game']['code']) }}" class="inline" onsubmit="return confirm('Cancel this game room?');">
                    @csrf
                    <button class="font-medium text-red-600 underline hover:text-red-700">Cancel Room</button>
                </form>
            </div>

            <div class="mt-5 rounded-2xl border border-emerald-200/60 bg-white/70 p-3 text-xs text-zinc-600">
                <span class="font-semibold text-emerald-800">💡 Testing 1v1 on one computer?</span>
                Open an <span class="font-semibold">Incognito / Private window</span>, log in with a second account, and open the invite link or enter the Game ID.
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        {{-- Board column --}}
        <div class="flex flex-col items-center">
            {{-- Opponent --}}
            <div class="mb-2 flex w-full max-w-[640px] items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-lg">
                    {{ $myColor === 'white' ? $state['players']['black']['avatar'] : $state['players']['white']['avatar'] }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">
                        {{ $myColor === 'white' ? $state['players']['black']['name'] : $state['players']['white']['name'] }}
                        <span class="text-xs font-normal text-zinc-400">
                            {{ ($myColor === 'white' ? $state['players']['black']['rating'] : $state['players']['white']['rating']) ?? '' }}
                            · {{ $myColor === 'white' ? 'Black' : 'White' }}
                        </span>
                    </p>
                </div>
                <div id="clock-{{ $myColor === 'white' ? 'black' : 'white' }}"
                     class="clock ml-auto rounded-xl bg-zinc-100 px-4 py-1.5 text-xl font-bold text-zinc-700">--:--</div>
            </div>

            <div class="board-frame w-full max-w-[640px]">
                <div id="board" aria-label="Chessboard"></div>
            </div>
            {{-- You --}}
            <div class="mt-2 flex w-full max-w-[640px] items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-lg">
                    {{ $myColor === 'white' ? $state['players']['white']['avatar'] : $state['players']['black']['avatar'] }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">
                        {{ $myColor === 'white' ? $state['players']['white']['name'] : $state['players']['black']['name'] }}
                        <span class="text-xs font-normal text-zinc-400">
                            {{ ($myColor === 'white' ? $state['players']['white']['rating'] : $state['players']['black']['rating']) ?? '' }}
                            · {{ $myColor }}
                        </span>
                    </p>
                    <p id="game-status" class="text-xs text-emerald-600"></p>
                </div>
                <div id="clock-{{ $myColor }}"
                     class="clock ml-auto rounded-xl bg-zinc-100 px-4 py-1.5 text-xl font-bold text-zinc-700">--:--</div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="flex flex-col gap-4">
            <div class="rounded-2xl border border-zinc-100 bg-white shadow-sm">
                <div class="border-b border-zinc-100 px-4 py-3 text-sm font-bold">Moves</div>
                <div id="move-list" class="move-list max-h-64 overflow-y-auto px-1 py-2 lg:max-h-96"></div>
            </div>

            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <div class="grid grid-cols-3 gap-2">
                    <button id="btn-flip" class="rounded-xl border border-zinc-200 px-3 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50">⇅ Flip</button>
                    <button id="btn-draw" class="rounded-xl border border-zinc-200 px-3 py-2.5 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50">½ Draw</button>
                    <button id="btn-resign" class="rounded-xl border border-red-200 px-3 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">⚑ Resign</button>
                </div>
                <p class="mt-3 text-center text-xs text-zinc-400">
                    Room <span class="font-mono font-semibold text-zinc-600">{{ $state['game']['code'] }}</span>
                    · {{ $state['game']['time_control'] }} · {{ $state['game']['mode'] === 'bot' ? 'vs Bot ('.$state['game']['difficulty'].')' : '1 vs 1' }}
                </p>
            </div>

            <a href="{{ route('home') }}" class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50">
                ← Leave game
            </a>
        </aside>
    </div>
</div>
@endsection
