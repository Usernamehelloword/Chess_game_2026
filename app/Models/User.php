<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'avatar', 'bio', 'rating', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function games(): array
    {
        $white = Game::query()->where('white_user_id', $this->id)->orderByDesc('created_at')->limit(25)->get();
        $black = Game::query()->where('black_user_id', $this->id)->orderByDesc('created_at')->limit(25)->get();

        return $white->merge($black)->sortByDesc('created_at')->values()->all();
    }

    public function avatarUrl(): string
    {
        return '//'.rawurlencode($this->avatar ?: '♞');
    }
}
