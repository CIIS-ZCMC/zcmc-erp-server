<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;


class CustomHttpException extends Exception
{
    protected int $status;

    public function __construct(string $message = "An error occurred", int $status = 400)
    {
        parent::__construct($message);
        $this->status = $status;
    }

    /**
     * Render the exception into an HTTP JSON response.
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage()
        ], $this->status);
    }

    // public function register()
    // {
    //     $this->renderable(function (CustomHttpException $e, $request) {
    //         return response()->json([
    //             'error' => $e->getMessage()
    //         ], $e->getStatusCode());
    //     });
    // }
}
