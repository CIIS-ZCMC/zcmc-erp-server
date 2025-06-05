<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAuthResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('session_id');

        $attempt = Auth::attempt($credentials);

        if (!$attempt) {
            return response()->json([
                'message' => "Invalid credentials",
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        // Check if user was authenticated but no session exists
        if (!$user || !$user->session) {
            return response()->json([
                'message' => "Session not found or invalid",
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->session->token;

        if (is_array($token)) {
            $token = json_encode($token);
        }

        // Sanitize token to prevent newline characters in headers
        $token = str_replace(["\r", "\n"], '', $token);

        // Create a cookie separately using the cookie helper
        $cookie = cookie()->make(
            env('COOKIE_NAME'),
            $token,
            1440,
            '/',
            env('SESSION_DOMAIN'),
            env('APP_ENV') !== 'local', // Secure in production
            false, // Not HttpOnly to allow JS access
            false, // Discard on client exit
            'lax' // SameSite policy
        );


        // Build the response and attach the cookie with withCookie
        $resource = new UserAuthResource($user);

        return $resource
            ->additional([
                'message' => "Successfully signin.",
                'meta' => ['redirect_to' => '/dashboard']
            ])
            ->response()
            ->setStatusCode(Response::HTTP_OK)
            ->withCookie($cookie);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => "Unauthorized",
            ], Response::HTTP_UNAUTHORIZED);
        }


        $resource = new UserAuthResource($user);

        return $resource
            ->additional([
                'message' => "Successfully signin.",
                'meta' => ['redirect_to' => '/dashboard']
            ]);
    }
    
    public function logout(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => "No authenticated user",
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 1. Revoke the current token (if using tokens)
        if (method_exists($user, 'currentAccessToken')) {
            $user->currentAccessToken()->delete();
        }

        // 2. Create an expired cookie to remove it from browser
        $cookie = Cookie::forget(
            env('COOKIE_NAME', 'auth_token'),  // Cookie name
            '/',                               // Path
            null                             // Domain (null for current)
        );

        // 3. Return response with cookie removal
        return response()->json([
            'message' => "Successfully logged out"
        ], Response::HTTP_OK)->withCookie($cookie);
    }
}
