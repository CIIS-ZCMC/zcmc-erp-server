<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAuthResource;
use App\Http\Resources\UserResource;
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

        $attempt = Auth::attempt($credentials);

        if (!$attempt) {
            return response()->json([
                'message' => "Invalid credentials",
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();
        $session = $user->session;

        $token = json_encode($session->token);

        return (new UserAuthResource( $user))
            ->additional([
                'message' => "Successfully signin."
            ])
            ->response()
            ->setStatusCode(Response::HTTP_OK)
            ->cookie(env('COOKIE_NAME'), $token, 30, '/', env('SESSION_DOMAIN'), false);
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
