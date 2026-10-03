@extends('layouts.app')

@section('title', 'How to Play Chess — Complete Beginner Rules, Moves & Guide | CHess')
@section('meta_description', 'Learn how to play chess online with our easy beginner guide. Understand piece moves, chess rules, board setup, castling, and key winning strategies.')
@section('meta_keywords', 'how to play chess, chess rules, chess moves, chess for beginners, learn chess, how chess works, learn how to play chess online, how to win at chess')
@section('page', 'how-to-play')

@section('structured_data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => [
    [
      '@type' => 'Question',
      'name' => 'How do you win a game of chess?',
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => "You win in chess by checkmating your opponent's king, meaning the king is under attack and has no legal escape. You can also win if your opponent runs out of time on their clock or resigns.",
      ],
    ],
    [
      '@type' => 'Question',
      'name' => 'Can pawns move backwards in chess?',
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => 'No, pawns can only move forward. They move one square forward (or two squares on their very first move) and capture diagonally forward.',
      ],
    ],
    [
      '@type' => 'Question',
      'name' => 'What is castling in chess?',
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => 'Castling is a special chess move allowing the king to move two squares toward a rook, and the rook hops over to the square next to the king. It can only be done if neither piece has moved and the path is clear.',
      ],
    ],
    [
      '@type' => 'Question',
      'name' => 'Can I play chess online without downloading?',
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => 'Yes! On CHess, you can play chess online for free directly in your web browser on mobile or desktop without downloading any app.',
      ],
    ],
  ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<div class="mx-auto max-w-3xl py-6">
    <div class="text-center sm:text-left">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
            ♟️ Chess for Beginners
        </span>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">How to Play Chess: Rules & Guide</h1>
        <p class="mt-2 text-zinc-600">A clear, complete guide to learn chess online — piece moves, essential rules, and smart tactics for beginners.</p>
    </div>

    <div class="mt-8 space-y-6">
        <!-- The Objective -->
        <section class="rounded-2xl border border-zinc-100 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-zinc-900">🎯 The Goal: How to Win at Chess</h2>
            <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                The primary objective in chess is to <strong>checkmate</strong> your opponent's king. Checkmate occurs when the king is placed under attack ("in check") and has no legal move to escape.
            </p>
            <p class="mt-2 text-sm leading-relaxed text-zinc-600">
                A chess game can also be won if your opponent runs out of time on their clock or resigns. Games end in a <strong>draw</strong> through stalemate, threefold repetition of moves, the 50-move rule, mutual agreement, or insufficient material to deliver checkmate.
            </p>
        </section>

        <!-- How Pieces Move -->
        <section class="rounded-2xl border border-zinc-100 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-zinc-900">♟️ How the Chess Pieces Move</h2>
            <p class="mt-1 text-xs text-zinc-500">Each piece has a distinct movement pattern on the 64-square chessboard:</p>
            <ul class="mt-4 space-y-3 text-sm text-zinc-600">
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♟️</span>
                    <div><strong class="text-zinc-900">Pawn:</strong> Moves one square forward (or two squares on its first move). Captures one square diagonally forward. If a pawn reaches the eighth rank, it promotes to any piece (usually a Queen).</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♞</span>
                    <div><strong class="text-zinc-900">Knight:</strong> Jumps in an "L-shape" (two squares in one direction, one square perpendicular). It is the only piece that can jump over other pieces.</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♝</span>
                    <div><strong class="text-zinc-900">Bishop:</strong> Slides diagonally across any number of open squares of the same color it starts on.</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♜</span>
                    <div><strong class="text-zinc-900">Rook:</strong> Slides horizontally or vertically across any number of unoccupied squares.</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♛</span>
                    <div><strong class="text-zinc-900">Queen:</strong> The most powerful piece on the board. Combines the moves of both the rook and bishop in any direction.</div>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-lg leading-none">♚</span>
                    <div><strong class="text-zinc-900">King:</strong> Moves one square in any direction. Protect your king at all costs. Can also perform <strong>castling</strong> with an unmoved rook for king safety.</div>
                </li>
            </ul>
        </section>

        <!-- Playing Online on CHess -->
        <section class="rounded-2xl border border-zinc-100 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-zinc-900">🎮 How to Play Chess Online on CHess</h2>
            <ol class="mt-3 list-decimal space-y-2.5 pl-5 text-sm text-zinc-600">
                <li>Go to the <a href="{{ route('play') }}" class="font-semibold text-emerald-600 underline">Play Online</a> page and pick <strong>1 vs 1 Multiplayer</strong> or <strong>Play vs Bot</strong>.</li>
                <li>Select your desired time control (e.g., 10+5 gives 10 minutes per side with a 5-second increment per move).</li>
                <li>Tap or click any piece to see its legal moves highlighted on the board, then select your target square.</li>
                <li>In multiplayer mode, share your <strong>Game Code</strong> with a friend to start an instant online chess match without installing anything.</li>
                <li>Keep an eye on the clock! The turn passes immediately, and timeout results in a loss.</li>
            </ol>
        </section>

        <!-- Beginner Tips -->
        <section class="rounded-2xl border border-zinc-100 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-zinc-900">💡 Smart Chess Strategies for Beginners</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-zinc-600">
                <li><strong>Control the Center:</strong> Place pawns and knights toward e4, d4, e5, and d5 to dominate the board.</li>
                <li><strong>Develop Your Pieces:</strong> Bring out your knights and bishops early before moving the queen.</li>
                <li><strong>Castle Early:</strong> Castling tucks your king safely into a corner and activates your rook.</li>
                <li><strong>Always Look for Threats:</strong> After every move by your opponent, ask yourself: <em>What square are they attacking now?</em></li>
            </ul>
        </section>

        <!-- Call to action -->
        <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 p-8 text-center text-white shadow-md">
            <h2 class="text-2xl font-bold">Ready to Practice Your Chess Moves?</h2>
            <p class="mt-2 text-sm text-emerald-100">Put your knowledge to work right now in a live online game or against the computer bot.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('bot.setup') }}" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50">
                    🤖 Practice vs Bot
                </a>
                <a href="{{ route('play') }}" class="rounded-xl border border-white/40 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                    ♟️ Play Multiplayer
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
