<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use Carbon\Carbon;

class CheckSystemExpired
{
    public function handle(Request $request, Closure $next)
    {
        $token = Token::first();
        
        // Jika belum ada token atau sudah expired
        if (!$token || Carbon::now()->gt($token->expired_at)) {
            // Redirect ke halaman aktivasi atau tampilkan modal
            return redirect()->route('activation.page')
                ->with('error', 'Sistem expired. Silakan aktivasi token.');
        }
        
        return $next($request);
    }
}