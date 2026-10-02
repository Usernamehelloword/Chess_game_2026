<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['game_id', 'move_number', 'color', 'uci', 'san', 'fen_before', 'fen_after'])]
class ChessMove extends Model
{
    protected $table = 'moves';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }
}