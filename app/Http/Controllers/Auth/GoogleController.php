<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

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

            
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
             
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar_url,
                ]);
            } else {
              
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar_url' => $googleUser->avatar_url,
                    'password' => null, 
                  
                ]);
                $user->assignRole('member');
            }

            // Login-kan user
            Auth::login($user);

            if ($user->hasRole('admin')) {
            return redirect('/admin'); 
        }

       
        return redirect('/app');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Login Gagal: ' . $e->getMessage());
        }
    }
}