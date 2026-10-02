@extends('layouts.app')

@section('title', 'Login — CHess')

@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        <div class="text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-2xl text-white">♞</span>
            <h1 class="mt-4 text-2xl font-bold">Welcome back</h1>
            <p class="mt-1 text-sm text-zinc-500">Sign in to play chess</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="text-sm font-medium text-zinc-700">Password</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-zinc-600">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-emerald-600">
                Remember me
            </label>
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Sign in
            </button>
        </form>

        <div class="mt-6 flex items-center justify-between text-sm">
            <a href="{{ route('password.request') }}" class="text-zinc-500 hover:text-emerald-600">Forgot password?</a>
            <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Create account →</a>
        </div>
    </div>
</div>
@endsection
