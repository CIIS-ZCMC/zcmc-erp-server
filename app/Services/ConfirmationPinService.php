<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ConfirmationPinService
{
    public function confirmPin($request)
    {
        // Get the current user and its area
        $curr_user = User::find($request->user()->id);
        $curr_user_authorization_pin = $curr_user->authorization_pin;

        if ($curr_user_authorization_pin !== $request->authorization_pin) {
            return response()->json(['message' => 'Invalid Authorization Pin'], Response::HTTP_BAD_REQUEST);
        }

        return true;
    }
}