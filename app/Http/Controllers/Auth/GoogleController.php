<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            DB::beginTransaction();

            $user = User::firstOrCreate(
                ['email' => $googleUser->email],
                [
                    'name'       => $googleUser->name,
                    'google_id'  => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                    'password'   => null,
                ]
            );

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            if (! $user->hasAnyRole(['admin', 'member'])) {
                $user->assignRole('member');
            }

            DB::commit();

            // Blokir login Google untuk akun admin
            if ($user->hasRole('admin')) {
                request()->session()->forget('google_login_panel');
                return redirect('/admin/login')
                    ->with('error', 'Login Google tidak tersedia untuk admin.');
            }

            // 🔥 FIX LOOP LOGIN
            $panel = Filament::getPanel('app');

            // WAJIB set panel dulu
            Filament::setCurrentPanel($panel);

            // Login pakai guard panel
            $panel->auth()->login($user);

            // Regenerate session
            request()->session()->regenerate();
            request()->session()->forget('google_login_panel');

            // Redirect langsung ke dashboard panel
            return redirect()->to($panel->getUrl());

        } catch (\Throwable $e) {
            DB::rollBack();

            request()->session()->forget('google_login_panel');
            return redirect('/app/login')
                ->with('error', 'Login Google gagal');
        }
    }
}
