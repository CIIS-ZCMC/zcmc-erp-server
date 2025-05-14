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
        try {
            // Forward authentication request to UMIS API
            $response = HttpRequestHelper::forwardRequestToExternalApi(
                endpoint: "auth-with-session-id",
                method: 'POST',
                data: $credentials
            );

            if(!$response->successful()){
                Log::error('UMIS authentication failed: ' . ($response->body() ?? 'No response body'));
                return false; // Return false for failed authentication
            }

            $responseData = $response->json();
            
            // Validate response data
            if (!isset($responseData['user_details']) || !isset($responseData['user_details']['employee_profile_id'])) {
                Log::error('Missing user_details or employee_profile_id in UMIS response: ' . json_encode($responseData));
                return false;
            }

            $user = User::find($responseData['user_details']['employee_profile_id']);

            if(!$user){
                Log::error('User not found with ID: ' . $responseData['user_details']['employee_profile_id']);
                return false;
            }

            $abilities = [];
            if (isset($responseData['permissions']['modules'])) {
                $modules = $responseData['permissions']['modules'];
                foreach($modules as $module){
                    if (isset($module['permissions']) && isset($module['code'])) {
                        foreach($module['permissions'] as $permission){
                            $abilities[] = $module['code'].':'.$permission;
                        }
                    }
                }
            }

            // Remove access token that may exist even if user is not active
            AccessToken::where('user_id', $user->id)->delete();

            // Create user session
            AccessToken::create([
                'user_id' => $user->id,
                'session_id' => $credentials['session_id'],
                'permissions' => $responseData['permissions'] ?? [],
                'authorization_pin' => $responseData['authorization_pin'] ?? null,
                'expire_at' => $responseData['session']['token_exp'] ?? now()->addDays(1),
                'token' => $responseData['session']['token'] ?? '',
                'abilities' => $abilities
            ]);
            
            $this->setUser($user);

            return true;
        } catch (\Exception $e) {
            Log::error('Exception during authentication: ' . $e->getMessage());
            return false;
        }
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }
    
        try {
            // 1. Check for the auth cookie
            $cookie = $this->request->cookies->get(env('COOKIE_NAME'));
            
            if (!$cookie) {
                Log::debug('No auth cookie found in request');
                return null; // No cookie found
            }

            // 2. Process the token from cookie
            $token = $cookie;
            
            // If it's a JSON string, decode it
            if (is_string($token) && $this->isValidJson($token)) {
                $token = json_decode($token, true);
            }
            
            // If it's an array after decoding, convert to string for DB comparison
            if (is_array($token)) {
                $token = json_encode($token);
            }
            
            // 3. Sanitize token
            $token = is_string($token) ? str_replace(["\r", "\n"], '', $token) : $token;
        
            // 4. Validate the token against your database
            $accessToken = AccessToken::where('token', $token)
                ->where('expire_at', '>', now())
                ->first();
        
            if (!$accessToken) {
                Log::debug('No valid access token found in database');
                return null;
            }
        
            // 5. Return the authenticated user
            $this->user = $this->provider->retrieveById($accessToken->user_id);
            
            // Make sure we have a user
            if ($this->user) {
                // Re-associate the access token with the user for easy access
                $accessToken->user_id = $this->user->id;
                $accessToken->save();
            }
            
            return $this->user;
        } catch (\Exception $e) {
            Log::error('Error in AuthCookieGuard->user(): ' . $e->getMessage());
            return null;
        }
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
        // Generate a secure remember token
        $token = hash('sha256', random_bytes(32));
        
        // Store the token in the user model
        $this->provider->updateRememberToken($user, $token);
        
        // Return the token for cookie storage
        return $token;
    }

    public function hasUser()
    {
        return !is_null($this->user);
    }
    
    /**
     * Helper method to check if a string is valid JSON
     * 
     * @param string $string The string to check
     * @return bool Whether the string is valid JSON
     */
    protected function isValidJson($string) 
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
