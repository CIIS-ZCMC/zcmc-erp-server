<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            Log::warning('Authentication failed: No authenticated user found');
            throw new AuthenticationException(
                'Unauthenticated.', ['auth_user_provider'], route('login')
            );
        }

        // Get the access token for the user
        $accessToken = $user->currentAccessToken();
        
        // Check if the token has the required abilities
        if (! $accessToken) {
            Log::warning("User {$user->id} has no access token");
            abort(403, 'Insufficient abilities.');
        }
        
        if (! $accessToken->can($abilities)) {
            Log::warning("User {$user->id} lacks required abilities: " . implode(', ', $abilities));
            abort(403, 'Insufficient abilities.');
        }

        Log::info("User {$user->id} authorized with abilities: " . implode(', ', $abilities));
        return $next($request);
    }
}