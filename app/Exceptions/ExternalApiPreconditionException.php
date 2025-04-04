<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The external API response has an unexpected structure.
 */
class ExternalApiPreconditionException extends Exception
{
    private array $context;

    public function __construct(string $message, Response $response)
    {
        parent::__construct("{$message}", 400);

        $this->context = [
            'response' => $response->body(),
            'stats' => $response->handlerStats(),
        ];
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $this->message], 400, []);
    }

    public function report(): void
    {
        Log::critical($this->message, $this->context);
    }
}
