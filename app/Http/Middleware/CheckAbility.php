<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class CheckAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$abilities
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$abilities)
    {
        // Get the authenticated user using your custom guard
        $user = auth()->user();

        if (! $user) {
            throw new AuthenticationException(
                'Unauthenticated.', ['auth_user_provider'], route('login')
            );
        }

        // Get the access token for the user
        $accessToken = $user->currentAccessToken();
        
        // Check if the token has the required abilities
        if (! $accessToken || ! $accessToken->can($abilities)) {
            abort(403, 'Insufficient abilities.');
        }

        return $next($request);
    }
}