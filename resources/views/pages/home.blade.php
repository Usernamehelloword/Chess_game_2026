@extends('layouts.app')

@section('title', 'Play Chess Online — Free Online Chess Game | CHess')
@section('meta_description', 'Play chess online for free! Enjoy real-time multiplayer chess with friends, challenge smart computer bots, and climb the live leaderboard. Play free chess online with no download required.')
@section('meta_keywords', 'play chess online, free chess game, online chess, multiplayer chess, play chess with friends, free online chess, play chess online free, online chess game')
@section('page', 'home')

@section('structured_data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'WebApplication',
  'name' => 'CHess - Play Chess Online',
  'url' => url('/'),
  'applicationCategory' => 'GameApplication',
  'genre' => 'Chess',
  'operatingSystem' => 'All',
  'offers' => [
    '@type' => 'Offer',
    'price' => '0',
    'priceCurrency' => 'USD',
  ],
  'description' => 'Play chess online for free. Real-time multiplayer chess with friends, intelligent computer chess bots, live rankings, and no download required.',
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<section class="grid items-center gap-10 py-8 lg:grid-cols-2 lg:py-16">
    <div class="fade-in">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
            ♟️ Free Online Chess Game
        </span>
        <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-zinc-900 sm:text-5xl lg:text-6xl">
            Play Chess Online Free.<br><span class="text-emerald-600">Think Ahead & Win.</span>
        </h1>
        <p class="mt-4 max-w-lg text-lg text-zinc-600">
            Play chess online with friends in real-time multiplayer matches, or test your strategy against an intelligent computer chess bot. A modern, free chess game right in your browser.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ auth()->check() ? route('lobby') : route('login') }}"
               class="group flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-7 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                ♟️ Play Chess with Friends
                <span class="transition group-hover:translate-x-0.5">→</span>
            </a>
            <a href="{{ auth()->check() ? route('bot.setup') : route('login') }}"
               class="group flex items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-white px-7 py-4 text-base font-semibold text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:text-emerald-700">
                🤖 Play vs Computer Bot
                <span class="transition group-hover:translate-x-0.5">→</span>
            </a>
        </div>

        <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 text-center">
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">Total Games</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $totalGames }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">Active Players</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $totalPlayers }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-100 bg-white p-4 shadow-sm">
                <dt class="text-xs font-medium text-zinc-500">vs Bot Games</dt>
                <dd class="text-2xl font-bold text-zinc-900">{{ $botGames }}</dd>
            </div>
        </dl>
    </div>

    <div class="fade-in">
        <div class="board-frame mx-auto max-w-md lg:max-w-lg">
            <div id="hero-board"
                 data-fen="r1bq1rk1/pp2bppp/2n1pn2/3p4/2PP4/2N1PN2/PP2BPPP/R1BQ1RK1 w - - 0 9"
                 aria-label="Interactive online chess board preview"></div>
        </div>
        <p class="mt-3 text-center text-xs text-zinc-400">Play chess online without downloading — every move synced live.</p>
    </div>
</section>

<!-- Feature Cards highlighting primary keywords -->
<section class="mt-12 grid gap-6 sm:grid-cols-3">
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">⚡</span>
        <h2 class="mt-2 font-semibold text-zinc-900">Multiplayer Chess Online</h2>
        <p class="mt-1 text-sm text-zinc-600">Play chess online with friends or challenge opponents in real-time with 7 customizable time controls from bullet to rapid.</p>
    </div>
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">🧠</span>
        <h2 class="mt-2 font-semibold text-zinc-900">Play Chess Against Computer</h2>
        <p class="mt-1 text-sm text-zinc-600">Practice your tactics against smart computer bots ranging from friendly beginner level to punishing master engine.</p>
    </div>
    <div class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm transition hover:shadow-md">
        <span class="text-2xl">📈</span>
        <h2 class="mt-2 font-semibold text-zinc-900">Online Chess Leaderboard</h2>
        <p class="mt-1 text-sm text-zinc-600">Climb the global rankings. Your Elo rating updates after every competitive match with detailed chess game stats.</p>
    </div>
</section>

<!-- Informational SEO Section: Long-tail & user-focused information -->
<section class="mt-14 rounded-3xl border border-zinc-100 bg-white p-8 sm:p-10 shadow-sm">
    <div class="max-w-2xl">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900">The Best Place to Play Chess Online for Free</h2>
        <p class="mt-2 text-sm text-zinc-600 leading-relaxed">
            Whether you want to play a free chess game against computer bots, compete in an online multiplayer chess game with friends, or study chess moves, CHess delivers a lightning-fast experience directly in your browser.
        </p>
    </div>

    <div class="mt-8 grid gap-6 sm:grid-cols-3">
        <div>
            <h3 class="font-semibold text-emerald-700">No Download Required</h3>
            <p class="mt-1 text-xs text-zinc-600 leading-relaxed">Play chess online mobile or desktop without installing apps. Instant board loads and zero bloat.</p>
        </div>
        <div>
            <h3 class="font-semibold text-emerald-700">Chess for Beginners</h3>
            <p class="mt-1 text-xs text-zinc-600 leading-relaxed">New to chess? Practice basic moves with our beginner bot or read our step-by-step <a href="{{ route('how-to-play') }}" class="underline hover:text-emerald-800">How to Play Chess</a> guide.</p>
        </div>
        <div>
            <h3 class="font-semibold text-emerald-700">Fair Rating System</h3>
            <p class="mt-1 text-xs text-zinc-600 leading-relaxed">Play rated chess games with automated Elo calculation to ensure competitive matches on the <a href="{{ route('leaderboard') }}" class="underline hover:text-emerald-800">chess leaderboard</a>.</p>
        </div>
    </div>
</section>

@if ($topPlayers->isNotEmpty())
<section class="mt-12 rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-zinc-900">Top Rated Chess Players</h2>
        <a href="{{ route('leaderboard') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View online chess leaderboard →</a>
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
