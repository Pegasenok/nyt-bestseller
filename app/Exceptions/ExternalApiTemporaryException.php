<?php

namespace App\Exceptions;

use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The request to an external API provider failed due to temporary unavailability,
 * such as service outages or network issues.
 * Retrying the request might resolve the issue; log it with context for further evaluation.
 */
class ExternalApiTemporaryException extends \Exception
{
    private array $context;

    public static int $retry = 0;

    public function __construct(string $message, RequestException|ConnectionException $previous)
    {
        parent::__construct("{$message}", $previous->getCode(), $previous);

        if ($previous instanceof ConnectionException) {
            $underlineException = $previous->getPrevious();
            if ($underlineException instanceof ConnectException) {
                $this->context = [
                    'response' => $underlineException->message,
                    'stats' => $underlineException->getHandlerContext(),
                    'retry' => self::$retry++,
                ];
            } else {
                $this->context = [
                    'response' => (string) $underlineException,
                ];
            }
        }
        if ($previous instanceof RequestException) {
            $this->context = [
                'response' => $previous->response->body(),
                'stats' => $previous->response->handlerStats(),
                'retry' => self::$retry++,
            ];
        }
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $this->message], 400, []);
    }

    public function context()
    {
        return $this->context;
    }

    public function report(): void
    {
        Log::warning($this->message, $this->context);
    }
}
