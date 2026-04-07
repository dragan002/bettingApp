<?php

namespace App\Http\Middleware;

use App\Models\PlayerToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $playerToken = PlayerToken::with('player')->where('token', $bearer)->first();

        if (! $playerToken) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->merge(['player' => $playerToken->player]);
        $request->attributes->set('player', $playerToken->player);

        return $next($request);
    }
}
