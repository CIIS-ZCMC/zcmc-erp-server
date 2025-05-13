<?php

namespace App\Auth;

use App\Models\AccessToken;
use App\Helpers\HttpRequestHelper;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AuthCookieGuard",
    description: "Custom authentication guard using cookies"
)]
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
        $response = HttpRequestHelper::forwardRequestToExternalApi(
            endpoint: "auth-with-session-id",
            method: 'POST',
            data: $credentials
        );

        if(!$response->successful()){
            return 'Failed to authenticate with UMIS';
        }
    
        $responseData = $response->json();

        $user = User::find($responseData['user_details']['employee_profile_id']);

        if(!$user){
            return response()->json(['message' => "Failed to authenticate user not found from the record."], 401);
        }

        // Remove access token that may exist even if user is not active
        AccessToken::where('user_id', $user->id)->delete();

        // Create user session
        AccessToken::create([
            'user_id' => $user->id,
            'session_id' => $credentials['session_id'],
            'permissions' => $responseData['permissions'],
            'authorization_pin' => $responseData['authorization_pin'],
            'expire_at' => $responseData['session']['token_exp'],
            'token' => $responseData['session']['token']
        ]);
        
        $this->setUser($user);

        return true;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }
    
        // 1. Check for the auth cookie
        $cookie = $this->request->cookie(env('COOKIE_NAME'));
        
        if (!$cookie) {
            return null; // No cookie found
        }

        $token = json_decode($cookie);
    
        // 3. Validate the token against your database
        $accessToken = AccessToken::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();
    
        if (!$accessToken) {
            return null;
        }
    
        // 4. Return the authenticated user
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
