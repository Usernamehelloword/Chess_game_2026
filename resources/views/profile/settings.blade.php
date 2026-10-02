@extends('layouts.app')

@section('title', 'Settings — CHess')

@section('content')
<div class="mx-auto max-w-xl py-8">
    <h1 class="text-2xl font-bold tracking-tight">Profile Settings</h1>

    <form method="POST" action="{{ route('settings.update') }}" class="mt-6 space-y-6 rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm">
        @csrf
        <div>
            <label for="name" class="text-sm font-medium text-zinc-700">Username</label>
            <input id="name" type="text" name="name" value="{{ old('name', $me->name) }}" required minlength="2" maxlength="32"
                   class="mt-1 w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="text-sm font-medium text-zinc-700">Avatar</span>
            <div class="mt-2 grid grid-cols-6 gap-2">
                @foreach ($avatars as $a)
                <label class="cursor-pointer">
                    <input type="radio" name="avatar" value="{{ trim($a) }}" class="peer sr-only" {{ trim(old('avatar', $me->avatar)) === trim($a) ? 'checked' : '' }}>
                    <div class="flex h-12 items-center justify-center rounded-xl border border-zinc-200 text-2xl transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-zinc-50">{{ $a }}</div>
                </label>
                @endforeach
            </div>
            @error('avatar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="bio" class="text-sm font-medium text-zinc-700">Bio <span class="text-zinc-400">(optional)</span></label>
            <textarea id="bio" name="bio" rows="3" maxlength="255" placeholder="Tell us about your chess style..."
                      class="mt-1 w-full resize-none rounded-xl border border-zinc-200 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">{{ old('bio', $me->bio) }}</textarea>
        </div>

        <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
            Save changes
        </button>
    </form>
</div>
@endsection
