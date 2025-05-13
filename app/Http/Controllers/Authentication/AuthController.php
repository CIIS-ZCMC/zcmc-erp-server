<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\AccessToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('session_id');

        // $credentials = ['session_id' => "e7d69679-69f0-4e82-a90e-cb341e8f0924"];

        return Auth::guard('auth_user_provider')->attempt($credentials);

        if (Auth::guard('auth_user_provider')->attempt($credentials)) {
            $user = Auth::guard('auth_user_provider')->user();
            $encrypted_token = "test123"; // Replace with your token generation logic

            // Sanitize the token to remove newlines
            $encrypted_token = str_replace(["\n", "\r"], '', $encrypted_token);

            // Encode the token as JSON
            $cookieValue = json_encode(['token' => $encrypted_token], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Create the access token in the database
            $access_token = AccessToken::create([
                'session_id' => $encrypted_token,
                'permissions' => [
                    "name" => 'item',
                    "access" => ['write', 'update', 'read', 'delete']
                ],
                'authorization_pin' => 123456,
                'user_id' => $user->id,
                'expire_at' => Carbon::now()->addMinutes(30)
            ]);

            // Set the cookie
            return response()->json([
                'data' => [
                    "user" => $user,
                    'access' => $access_token
                ],
                'message' => "Success login",
            ], Response::HTTP_OK)
            ->cookie("apms-cookie", $cookieValue, 30, '/', env('SESSION_DOMAIN'), false);
        }

        return response()->json([
            'message' => "Invalid credentials",
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'message' => "Unauthorized",
            ], Response::HTTP_UNAUTHORIZED);
        }
    
        return response()->json([
            'data' => $user,
            'message' => "Success"
        ], Response::HTTP_OK);
    }
}
