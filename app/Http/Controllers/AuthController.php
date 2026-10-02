<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class AuthController extends Controller
{
    public static function showRegister()
    {
        return view('auth.register');
    }

    public static function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:32'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via model cast
            'rating' => 1200,
            'avatar' => '♞',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/play');
    }

    public static function showLogin()
    {
        return view('auth.login');
    }

    public static function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput();
        }

        $request->session()->regenerate();

        return redirect()->intended('/play');
    }

    public static function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // ------------------------------------------------------------------
    // Password reset (mail driver is "log" in local dev: the link is
    // written to storage/logs/laravel.log)
    // ------------------------------------------------------------------

    public static function showForgot()
    {
        return view('auth.forgot');
    }

    public static function sendResetLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $user = User::query()->where('email', $data['email'])->first();
        if ($user) {
            $token = Str::random(64);
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => hash('sha256', $token), 'created_at' => now()],
            );

            $url = url('/reset-password?token='.$token.'&email='.urlencode($user->email));

            Mail::raw("Reset your CHess password:\n\n{$url}\n\nThis link expires in 60 minutes.", function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your CHess password');
            });
        }

        return back()->with('status', 'If an account exists for that email, a reset link has been sent.');
    }

    public static function showReset(Request $request)
    {
        return view('auth.reset', ['token' => $request->query('token'), 'email' => $request->query('email')]);
    }

    public static function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', hash('sha256', $data['token']))
            ->first();

        $expired = ! $record || now()->diffInMinutes($record->created_at) > 60;
        if (! $record || $expired) {
            return back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
        }

        $user = User::query()->where('email', $data['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return redirect()->route('login')->with('status', 'Password updated. You can now sign in.');
    }
}
