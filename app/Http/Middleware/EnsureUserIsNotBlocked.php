<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBlocked
{
    /**
     * Desloga imediatamente um usuário bloqueado pelo admin, mesmo que a
     * sessão já estivesse ativa antes do bloqueio.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->estaBloqueado()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home');
        }

        return $next($request);
    }
}
