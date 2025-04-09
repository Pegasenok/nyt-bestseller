<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e) {
            // do not catch exceptions that are properly rendered by framework elsewhere
            if ($e instanceof ValidationException) {
                return null;
            }

            return response()->json(['status' => false, 'message' => 'Something went wrong. Retry in 5 minutes.'], 400, []);
        });
    }
}
