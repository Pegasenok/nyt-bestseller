<?php

namespace App\Exceptions;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class ExternalApiViolationException extends \Exception
{
    private array $context;
    private UuidV4 $uuid;

    public function __construct(string $message, RequestException $previous)
    {
        $this->uuid = Uuid::v4();

        parent::__construct("{$message} {$this->uuid}", $previous->getCode(), $previous);

        $this->context = [
            'response' => $previous->response->json() ?? $previous->response->body(),
            'uuid' => $this->uuid->toRfc4122(),
        ];
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
        Log::error($this->message, $this->context);
    }
}
