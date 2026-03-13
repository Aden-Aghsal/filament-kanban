<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGoogleLoginAppPanel
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = optional($request->route())->getName();

        if ($routeName === 'login.google') {
            if ($request->query('panel') !== 'app') {
                return redirect('/app/login')
                    ->with('error', 'Login Google hanya tersedia untuk panel App.');
            }

            $request->session()->put('google_login_panel', 'app');
        }

        if ($routeName === 'login.google.callback') {
            if ($request->session()->get('google_login_panel') !== 'app') {
                return redirect('/app/login')
                    ->with('error', 'Login Google hanya tersedia untuk panel App.');
            }
        }

        return $next($request);
    }
}
