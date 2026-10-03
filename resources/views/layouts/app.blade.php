<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="ls1eSZ1EQWakUXWtHLZtDcYRC0PNKuHd6QQ3qKASDaU" />
    <title>@yield('title', 'Play Chess Online — Free Online Chess Game | CHess')</title>
    <meta name="description" content="@yield('meta_description', 'Play chess online for free. Enjoy multiplayer chess matches with friends, challenge smart computer chess bots, climb the global leaderboard, and learn chess rules.')">
    <meta name="keywords" content="@yield('meta_keywords', 'play chess online, free online chess, chess game online, multiplayer chess, chess vs computer, play chess with friends')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="CHess">
    <meta property="og:title" content="@yield('og_title', 'Play Chess Online — Free Online Chess Game | CHess')">
    <meta property="og:description" content="@yield('og_description', 'Play chess online for free. Enjoy multiplayer chess matches with friends, challenge smart computer chess bots, and climb the leaderboard.')">
    <meta property="og:image" content="{{ asset('logo.svg') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', 'Play Chess Online — Free Online Chess Game | CHess')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Play chess online for free. Enjoy multiplayer chess matches with friends, challenge smart computer chess bots, and climb the leaderboard.')">
    <meta name="twitter:image" content="{{ asset('logo.svg') }}">

    @yield('structured_data')

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FAFAF8] font-sans text-zinc-900 antialiased" data-page="@yield('page', '')" @yield('body-data', '')>

<header class="sticky top-0 z-40 border-b border-zinc-100 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('logo.svg') }}" alt="CHess Logo" class="h-9 w-9 rounded-xl shadow-sm">
            <span class="text-lg font-bold tracking-tight text-zinc-900">CHESS</span>
        </a>

        <div class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Home</a>
            <a href="{{ route('play') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Play</a>
            <a href="{{ auth()->check() ? route('games.mine') : route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Games</a>
            <a href="{{ route('leaderboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Leaderboard</a>
            <a href="{{ route('how-to-play') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">How to Play</a>
        </div>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('profile.show', auth()->id()) }}" class="hidden items-center gap-2 rounded-lg px-2 py-1.5 transition hover:bg-zinc-100 sm:flex">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-base">{{ auth()->user()->avatar }}</span>
                    <span class="text-sm font-semibold">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-zinc-400">{{ auth()->user()->rating }}</span>
                </a>
                <a href="{{ route('settings') }}" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 md:block">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100">Login</a>
                <a href="{{ route('register') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Sign Up</a>
            @endauth
        </div>
    </nav>
</header>

@if (session('status'))
    <div class="mx-auto mt-4 max-w-6xl px-4 sm:px-6">
        <div class="fade-in rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    </div>
@endif

<main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-8 sm:px-6">
    @yield('content')
</main>

<footer class="border-t border-zinc-100 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-6 text-sm text-zinc-500 sm:flex-row sm:px-6">
        <span class="flex items-center gap-2"><img src="{{ asset('logo.svg') }}" alt="CHess Logo" class="h-5 w-5 rounded"> <strong class="text-zinc-700">CHESS</strong> — Play Chess. Think Ahead.</span>
        <span>&copy; {{ date('Y') }} CHess. Built with Laravel.</span>
    </div>
</footer>

</body>
</html>
