<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     *
     */
    protected function redirectTo(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthorized',
        ], Response::HTTP_UNAUTHORIZED);

    }
}
