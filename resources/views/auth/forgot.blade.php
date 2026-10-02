@extends('layouts.app')

@section('title', 'Forgot Password — CHess')

@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold">Forgot your password?</h1>
        <p class="mt-1 text-sm text-zinc-500">Enter your email and we will send you a reset link.</p>

        <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label for="email" class="text-sm font-medium text-zinc-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            </div>
            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Send reset link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-700">← Back to login</a>
        </p>
    </div>
</div>
@endsection
