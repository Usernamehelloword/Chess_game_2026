<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    private function registerUser(string $name): void
    {
        // Create directly: posting to /register while a user is already logged in
        // is blocked by the "guest" middleware, so we seed users via the model
        // for flow tests and cover the HTTP registration endpoint separately.
        User::query()->create([
            'name' => $name,
            'email' => $name.'@test.local',
            'password' => 'password',
            'rating' => 1200,
        ]);
    }

    public function test_http_registration_works(): void
    {
        $this->post('/register', [
            'name' => 'newplayer',
            'email' => 'newplayer@test.local',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/play');

        $this->assertDatabaseHas('users', ['email' => 'newplayer@test.local']);
    }

    public function test_home_and_public_pages_render(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/play')->assertStatus(200);
        $this->get('/how-to-play')->assertStatus(200);
        $this->get('/leaderboard')->assertStatus(200);
    }

    public function test_guests_are_redirected_from_play_routes(): void
    {
        $this->get('/lobby')->assertRedirect('/login');
        $this->post('/games', ['time_control' => '5+0'])->assertRedirect('/login');
    }

    public function test_full_multiplayer_flow(): void
    {
        $this->registerUser('anna');
        $this->registerUser('ben');
        $anna = User::where('name', 'anna')->first();
        $ben = User::where('name', 'ben')->first();

        // Anna creates a waiting room.
        $response = $this->actingAs($anna)->post('/games', ['time_control' => '5+0']);
        $response->assertRedirect();
        $code = basename($response->headers->get('Location'));
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{6}$/', $code);

        $game = Game::query()->where('code', $code)->first();
        $this->assertNotNull($game);
        $this->assertSame(Game::STATUS_WAITING, $game->status);

        $this->actingAs($anna)->get('/games/'.$code)->assertOk();
        $this->actingAs($anna)->get('/games/'.$code.'/waiting')->assertJson(['ok' => true, 'status' => 'waiting']);

        // Ben joins with the code (case-insensitive).
        $this->actingAs($ben)->post('/join', ['code' => strtolower($code)])->assertRedirect('/games/'.$code);
        $this->assertDatabaseHas('games', ['code' => $code, 'status' => 'active']);

        // Ben is black, so moving first is rejected.
        $this->actingAs($ben)->post("/games/{$code}/move", ['uci' => 'e7e5'])->assertStatus(409);

        // 1. e4 e5
        $this->actingAs($anna)->post("/games/{$code}/move", ['uci' => 'e2e4'])
            ->assertOk()->assertJson(['ok' => true, 'san' => 'e4']);
        $this->actingAs($ben)->post("/games/{$code}/move", ['uci' => 'e7e5'])
            ->assertOk()->assertJson(['ok' => true, 'san' => 'e5']);

        // Illegal moves are rejected.
        $this->actingAs($anna)->post("/games/{$code}/move", ['uci' => 'e4e6'])
            ->assertStatus(422)->assertJson(['ok' => false]);

        // Ben cannot move twice in a row.
        $this->actingAs($ben)->post("/games/{$code}/move", ['uci' => 'g8f6'])->assertStatus(409);

        // State endpoint returns both moves.
        $this->actingAs($anna)->get("/games/{$code}/state?since=0")
            ->assertOk()
            ->assertJsonPath('state.move_count', 2)
            ->assertJsonPath('state.turn', 'white');

        // Ben resigns; Anna (white) wins.
        $this->actingAs($ben)->post("/games/{$code}/resign")->assertOk();

        $this->assertDatabaseHas('games', ['code' => $code, 'status' => 'finished', 'winner' => 'white']);
        $this->assertDatabaseHas('moves', ['game_id' => $game->id, 'move_number' => 2, 'san' => 'e5']);

        $this->assertGreaterThan(1200, $anna->fresh()->rating);
        $this->assertLessThan(1200, $ben->fresh()->rating);
        $this->assertSame(1, $anna->fresh()->wins);
        $this->assertSame(1, $ben->fresh()->losses);
    }

    public function test_bot_game_flow(): void
    {
        $this->registerUser('carl');
        $carl = User::where('name', 'carl')->first();

        $response = $this->actingAs($carl)->post('/games', [
            'mode' => 'bot',
            'difficulty' => 'beginner',
            'time_control' => '10+0',
            'color' => 'white',
        ]);
        $response->assertRedirect();
        $code = basename($response->headers->get('Location'));

        $game = Game::query()->where('code', $code)->first();
        $this->assertNotNull($game);
        $this->assertSame(Game::STATUS_ACTIVE, $game->status);
        $this->assertSame('bot', $game->black_side);

        // 1. e4 by the user...
        $this->actingAs($carl)->post("/games/{$code}/move", ['uci' => 'e2e4'])
            ->assertOk()->assertJson(['ok' => true, 'san' => 'e4', 'finished' => false]);

        // ...then the bot answers.
        $this->actingAs($carl)->post("/games/{$code}/bot-move")
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('finished', false);

        $this->assertCount(2, $game->moves());

        // Asking for a bot move when it is the user's turn fails politely.
        $this->actingAs($carl)->post("/games/{$code}/bot-move")->assertStatus(409);
    }

    public function test_fool_mate_finishes_multiplayer_game(): void
    {
        $this->registerUser('dana');
        $this->registerUser('evan');
        $dana = User::where('name', 'dana')->first();
        $evan = User::where('name', 'evan')->first();

        $response = $this->actingAs($dana)->post('/games', ['time_control' => '3+0']);
        $code = basename($response->headers->get('Location'));
        $this->actingAs($evan)->post('/join', ['code' => $code])->assertRedirect();

        $this->actingAs($dana)->post("/games/{$code}/move", ['uci' => 'f2f3']);
        $this->actingAs($evan)->post("/games/{$code}/move", ['uci' => 'e7e5']);
        $this->actingAs($dana)->post("/games/{$code}/move", ['uci' => 'g2g4']);
        $this->actingAs($evan)->post("/games/{$code}/move", ['uci' => 'd8h4'])
            ->assertOk()->assertJson(['ok' => true, 'finished' => true, 'winner' => 'black', 'reason' => 'checkmate']);

        $this->assertDatabaseHas('games', [
            'code' => $code, 'status' => 'finished',
            'winner' => 'black', 'result_reason' => 'checkmate',
        ]);
    }

    public function test_draw_offer_and_acceptance(): void
    {
        $this->registerUser('finn');
        $this->registerUser('gina');
        $finn = User::where('name', 'finn')->first();
        $gina = User::where('name', 'gina')->first();

        $response = $this->actingAs($finn)->post('/games', ['time_control' => '5+0']);
        $code = basename($response->headers->get('Location'));
        $this->actingAs($gina)->post('/join', ['code' => $code])->assertRedirect();

        $this->actingAs($finn)->post("/games/{$code}/draw", ['action' => 'offer'])->assertOk();
        $this->actingAs($gina)->get("/games/{$code}/state")
            ->assertJsonPath('state.game.draw_offered_by', 'white');

        $this->actingAs($gina)->post("/games/{$code}/draw", ['action' => 'accept'])->assertOk();
        $this->assertDatabaseHas('games', ['code' => $code, 'status' => 'finished', 'winner' => 'draw']);
    }
}