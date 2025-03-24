<?php

namespace App\Auth;

use App\Models\AccessToken;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;

class AuthCookieGuard implements Guard
{
    protected $provider;
    protected $request;
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function guest()
    {
        return !$this->check();
    }
    
    public function attempt(array $credentials = [], $remember = false)
    {
        // Retrieve the user by credentials (e.g., email)
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user && $this->provider->validateCredentials($user, $credentials)) {
            // Log the user in
            $this->setUser($user);

            // Optionally, set a "remember me" cookie
            if ($remember) {
                $this->createRememberToken($user);
            }

            return true;
        }

        return false;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        // Retrieve the token from the cookie
        $cookie = $this->request->cookie('apms-cookie');
        if (!$cookie) {
            return null;
        }

        $tokenData = json_decode($cookie, true);
        $token = $tokenData['token'] ?? null;

        if (!$token) {
            return null;
        }

        // Retrieve the access token from the AccessToken table
        $accessToken = AccessToken::where('session_id', $token)
            ->where('expire_at', '>', now())
            ->first();

        if (!$accessToken) {
            return null;
        }

        // Retrieve the user from the User table using the user_id from AccessToken
        $this->user = $this->provider->retrieveById($accessToken->user_id);

        return $this->user;
    }

    public function id()
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    public function validate(array $credentials = [])
    {
        // This method is not needed for your use case
        return false;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    protected function createRememberToken($user)
    {
        // Implement "remember me" token logic if needed
    }

    public function hasUser()
    {

    }
}
