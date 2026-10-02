@extends('layouts.app')

@section('title', 'Sign Up — CHess')

@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        <div class="text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-2xl text-white">♞</span>
            <h1 class="mt-4 text-2xl font-bold">Create your account</h1>
            <p class="mt-1 text-sm text-zinc-500">Start with a 1200 rating</p>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="name" class="text-sm font-medium text-zinc-700">Username</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus minlength="2" maxlength="32"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="text-sm font-medium text-zinc-700">Password</label>
                <input id="password" type="password" name="password" required minlength="6"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-medium text-zinc-700">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="6"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            </div>
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Create account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-700">Sign in</a>
        </p>
    </div>
</div>
@endsection
