<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code', 'mode', 'status',
    'white_user_id', 'black_user_id', 'white_side', 'black_side',
    'winner', 'result_reason', 'time_control', 'time_base', 'time_increment',
    'difficulty', 'rated', 'white_clock_ms', 'black_clock_ms', 'draw_offered_by',
    'started_at', 'finished_at',
])]
class Game extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ABANDONED = 'abandoned';

    public const MODE_MULTI = 'multi';
    public const MODE_BOT = 'bot';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'rated' => 'boolean',
        ];
    }

    public function whiteUser(): ?User
    {
        return $this->white_user_id ? User::find($this->white_user_id) : null;
    }

    public function blackUser(): ?User
    {
        return $this->black_user_id ? User::find($this->black_user_id) : null;
    }

    public function moves(): array
    {
        return ChessMove::query()
            ->where('game_id', $this->id)
            ->orderBy('move_number')
            ->get()
            ->all();
    }

    public function isBotGame(): bool
    {
        return $this->mode === self::MODE_BOT;
    }

    public function botColor(): ?string
    {
        if (! $this->isBotGame()) {
            return null;
        }

        return $this->white_side === 'bot' ? 'white' : 'black';
    }
}