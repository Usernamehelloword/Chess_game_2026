<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin account (login: admin@chess.local / password)
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@chess.local',
            'password' => 'password',
            'avatar' => '👑',
            'rating' => 1500,
            'is_admin' => true,
            'bio' => 'Platform administrator.',
        ]);

        // A few demo players so the leaderboard looks alive.
        $demo = [
            ['Alice', 'alice@chess.local', 1850, '♛', 'Tactical player. I love sharp Sicilians.'],
            ['David', 'david@chess.local', 1780, '♜', null],
            ['Kimlay', 'kimlay@chess.local', 1725, '♞', 'Endgame enthusiast.'],
            ['John', 'john@chess.local', 1690, '♝', null],
            ['Sofia', 'sofia@chess.local', 1610, '★', 'Blitz only, no takebacks!'],
            ['Marcus', 'marcus@chess.local', 1545, '⚡', null],
            ['Elena', 'elena@chess.local', 1490, '🛡', 'Defense first.'],
            ['Tom', 'tom@chess.local', 1385, '🎯', null],
            ['Nina', 'nina@chess.local', 1310, '🦊', 'Just here to have fun.'],
        ];

        foreach ($demo as [$name, $email, $rating, $avatar, $bio]) {
            User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'avatar' => $avatar,
                'rating' => $rating,
                'bio' => $bio,
            ]);
        }
    }
}
