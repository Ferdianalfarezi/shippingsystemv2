<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();
                
                // Redirect berdasarkan role
                if ($user->role === 'lp') {
                    return redirect()->route('shippings.checkingLp');
                }
                
                if ($user->role === 'scanner') {
                    return redirect()->route('preparations.scan');
                }
                
                return redirect()->route('preparations.index');
            }
        }

        return $next($request);
    }
}