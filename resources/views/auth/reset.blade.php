@extends('layouts.app')

@section('title', 'Reset Password — CHess')

@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Choose a new password</h1>

        <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="text-sm font-medium text-zinc-700">New password</label>
                <input id="password" type="password" name="password" required minlength="6"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-medium text-zinc-700">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="6"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            </div>
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Update password
            </button>
        </form>
    </div>
</div>
@endsection
