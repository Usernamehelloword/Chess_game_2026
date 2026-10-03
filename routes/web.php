<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PlayController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------
// Public pages
// ---------------------------------------------------------------------

Route::get('/', fn () => PagesController::home())->name('home');
Route::get('/play', fn () => PagesController::play())->name('play');
Route::get('/how-to-play', fn () => PagesController::howToPlay())->name('how-to-play');
Route::get('/leaderboard', fn () => PagesController::leaderboard())->name('leaderboard');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => route('play'), 'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => route('how-to-play'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('leaderboard'), 'priority' => '0.8', 'changefreq' => 'daily'],
        ['loc' => route('bot.setup'), 'priority' => '0.8', 'changefreq' => 'weekly'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url>';
        $xml .= '<loc>'.htmlspecialchars($url['loc']).'</loc>';
        $xml .= '<changefreq>'.$url['changefreq'].'</changefreq>';
        $xml .= '<priority>'.$url['priority'].'</priority>';
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'text/xml');
});

// ---------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------

Route::middleware('guest')->group(function () {
    Route::get('/register', fn () => AuthController::showRegister())->name('register');
    Route::post('/register', fn (Request $r) => AuthController::register($r))->name('register.post');

    Route::get('/login', fn () => AuthController::showLogin())->name('login');
    Route::post('/login', fn (Request $r) => AuthController::login($r))->name('login.post');

    Route::get('/forgot-password', fn () => AuthController::showForgot())->name('password.request');
    Route::post('/forgot-password', fn (Request $r) => AuthController::sendResetLink($r))->name('password.email');

    Route::get('/reset-password', fn (Request $r) => AuthController::showReset($r))->name('password.reset');
    Route::post('/reset-password', fn (Request $r) => AuthController::resetPassword($r))->name('password.update');
});

Route::post('/logout', fn (Request $r) => AuthController::logout($r))->middleware('auth')->name('logout');

// ---------------------------------------------------------------------
// Play (auth required)
// ---------------------------------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/lobby', fn (Request $r) => PlayController::lobby($r))->name('lobby');
    Route::get('/join', fn () => PlayController::joinForm())->name('join.form');
    Route::post('/join', fn (Request $r) => GamesController::join($r))->name('join.post');
    Route::get('/bot', fn () => PlayController::botSetup())->name('bot.setup');
    Route::get('/my-games', fn () => PlayController::myGames())->name('games.mine');

    Route::post('/games', fn (Request $r) => GamesController::create($r))->name('games.create');

    Route::get('/games/{code}', fn (Request $r, string $code) => GamesController::show($r, $code))->name('games.show');
    Route::get('/games/{code}/state', fn (Request $r, string $code) => GamesController::state($r, $code))->name('games.state');
    Route::get('/games/{code}/waiting', fn (Request $r, string $code) => GamesController::waitingState($r, $code))->name('games.waiting');
    Route::post('/games/{code}/move', fn (Request $r, string $code) => GamesController::move($r, $code))->name('games.move');
    Route::post('/games/{code}/bot-move', fn (Request $r, string $code) => GamesController::botMove($r, $code))->name('games.bot-move');
    Route::post('/games/{code}/resign', fn (Request $r, string $code) => GamesController::resign($r, $code))->name('games.resign');
    Route::post('/games/{code}/timeout', fn (Request $r, string $code) => GamesController::timeout($r, $code))->name('games.timeout');
    Route::post('/games/{code}/draw', fn (Request $r, string $code) => GamesController::draw($r, $code))->name('games.draw');
    Route::post('/games/{code}/rematch', fn (Request $r, string $code) => GamesController::rematch($r, $code))->name('games.rematch');

    // Profile & settings
    Route::get('/profile/{id}', fn (Request $r, int $id) => ProfileController::show($r, $id))->name('profile.show');
    Route::get('/settings', fn () => ProfileController::settings())->name('settings');
    Route::post('/settings', fn (Request $r) => ProfileController::updateSettings($r))->name('settings.update');

    // Admin
    Route::get('/admin', fn () => AdminController::dashboard())->name('admin.dashboard');
    Route::post('/admin/users/{id}/toggle-admin', fn (Request $r, int $id) => AdminController::toggleAdmin($r, $id))->name('admin.toggle-admin');
});

