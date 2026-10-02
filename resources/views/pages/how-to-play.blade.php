@extends('layouts.app')

@section('title', 'How to Play — CHess')

@section('content')
<div class="mx-auto max-w-3xl py-6">
    <h1 class="text-3xl font-bold tracking-tight">How to Play Chess</h1>
    <p class="mt-2 text-zinc-600">A quick primer for new players — and a refresher for everyone else.</p>

    <div class="mt-8 space-y-6">
        <section class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">🎯 The Goal</h2>
            <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                Checkmate your opponent's king — put it under attack with no legal escape.
                You can also win if your opponent resigns or runs out of time. Games can end in a draw by
                stalemate, agreement, threefold repetition, the fifty-move rule, or insufficient material.
            </p>
        </section>

        <section class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">♟️ How the Pieces Move</h2>
            <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                <li><strong class="text-zinc-800">Pawn</strong> — moves one square forward (two on its first move), captures diagonally. Reaching the last rank promotes it to any piece.</li>
                <li><strong class="text-zinc-800">Knight</strong> — jumps in an L-shape, leaping over other pieces.</li>
                <li><strong class="text-zinc-800">Bishop</strong> — slides diagonally any distance.</li>
                <li><strong class="text-zinc-800">Rook</strong> — slides horizontally or vertically any distance.</li>
                <li><strong class="text-zinc-800">Queen</strong> — combines rook and bishop: any direction, any distance.</li>
                <li><strong class="text-zinc-800">King</strong> — one square in any direction. It can also castle with a rook once per game.</li>
            </ul>
        </section>

        <section class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">🎮 Playing on CHess</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-zinc-600">
                <li>Click <strong>Play</strong> and choose <strong>1 vs 1</strong> or <strong>vs Bot</strong>.</li>
                <li>Pick a time control (e.g. 10+5 means 10 minutes plus a 5-second bonus per move) and start.</li>
                <li>Tap a piece to see its legal moves, then tap a highlighted square to move.</li>
                <li>In 1 vs 1, share the <strong>Game ID</strong> with a friend so they can join your room.</li>
                <li>The clock switches after every move. If your clock hits zero, you lose on time!</li>
            </ol>
        </section>

        <section class="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">💡 Quick Tips</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-zinc-600">
                <li>Control the center with your pawns and knights early.</li>
                <li>Castle within the first ten moves to keep your king safe.</li>
                <li>Don't bring your queen out too early — develop smaller pieces first.</li>
                <li>Before every move, check what your opponent's last move threatens.</li>
            </ul>
        </section>
    </div>
</div>
@endsection
