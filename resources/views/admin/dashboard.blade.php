@extends('layouts.app')

@section('title', 'Admin — CHess')

@section('content')
<div class="py-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Admin</span>
    </div>

    {{-- Stat cards --}}
    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['Total Users', $totalUsers, '👥'],
            ['Online (5m)', $onlineUsers, '🟢'],
            ['Total Games', $totalGames, '♟️'],
            ['Games Today', $gamesToday, '📅'],
            ['Bot Games', $botGames, '🤖'],
            ['Multiplayer', $multiGames, '⚔️'],
        ] as [$label, $value, $icon])
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm">
            <div class="text-xl">{{ $icon }}</div>
            <p class="mt-1 text-xs text-zinc-500">{{ $label }}</p>
            <p class="text-2xl font-bold">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        {{-- Chart --}}
        <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="font-bold">Games per day (last 7 days)</h2>
            <div class="mt-6 flex h-40 items-end gap-3">
                @foreach ($gamesPerDay as $day)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-zinc-500">{{ $day['count'] }}</span>
                        <div class="w-full rounded-t-lg bg-emerald-500 transition-all" style="height: {{ max(4, (int) (100 * $day['count'] / $maxCount)) }}%"></div>
                        <span class="text-[10px] text-zinc-400">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent users --}}
        <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="font-bold">Newest users</h2>
            <ul class="mt-4 space-y-2">
                @forelse ($recentUsers as $user)
                <li class="flex items-center gap-3 rounded-xl border border-zinc-50 px-3 py-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">{{ $user->avatar }}</span>
                    <a href="{{ route('profile.show', $user->id) }}" class="text-sm font-medium hover:text-emerald-600">{{ $user->name }}</a>
                    <span class="ml-auto font-mono text-xs text-zinc-400">{{ $user->rating }}</span>
                    <form method="POST" action="{{ route('admin.toggle-admin', $user->id) }}">
                        @csrf
                        <button class="rounded-lg px-2 py-1 text-[11px] font-semibold {{ $user->is_admin ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }} transition hover:opacity-80">
                            {{ $user->is_admin ? 'Admin' : 'Make admin' }}
                        </button>
                    </form>
                </li>
                @empty
                <li class="py-6 text-center text-sm text-zinc-400">No users yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Recent games --}}
    <div class="mt-8 rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
        <h2 class="font-bold">Recent games</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-100 text-xs uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="py-2 pr-4">Code</th>
                        <th class="py-2 pr-4">Mode</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Time</th>
                        <th class="py-2">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-50">
                    @forelse ($recentGames as $game)
                    <tr>
                        <td class="py-2 pr-4 font-mono font-semibold">{{ $game->code }}</td>
                        <td class="py-2 pr-4 text-zinc-500">{{ $game->mode === 'bot' ? 'Bot ('.$game->difficulty.')' : '1v1' }}</td>
                        <td class="py-2 pr-4">
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $game->status === 'finished' ? 'bg-zinc-100 text-zinc-500' : ($game->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">{{ $game->status }}</span>
                        </td>
                        <td class="py-2 pr-4 font-mono text-xs text-zinc-500">{{ $game->time_control }}</td>
                        <td class="py-2 text-xs text-zinc-400">{{ $game->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-zinc-400">No games yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
