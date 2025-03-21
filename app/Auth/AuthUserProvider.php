<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier (e.g., ID).
     */
    public function retrieveById($identifier)
    {
        // $token = AccessToken::where('user_id', $identifier)->first();

        // return $token ? User::find($token->user_id) : null;
    }

    /**
     * Retrieve a user by their unique token.
     */
    public function retrieveByToken($identifier, $token)
    {
        // $session = AccessToken::where('user_id', $identifier)
        //     ->where('token', $token)
        //     ->first();

        // return $session ? \App\Models\User::find($session->user_id) : null;
    }

    /**
     * Update the user's "remember me" token.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Update token in access_tokens table instead of users
        // AccessToken::updateOrCreate(
        //     ['user_id' => $user->id],
        //     ['token' => $token]
        // );
    }

    /**
     * Retrieve a user by given credentials (e.g., email, username).
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['email'])) {
            return null;
        }

        return User::where('email', $credentials['email'])->first();
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->password);
    }
    
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        if ($force || Hash::needsRehash($user->password)) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }
    }
}
