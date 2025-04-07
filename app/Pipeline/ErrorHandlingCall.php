<?php

namespace App\Pipeline;

use App\DTO\HttpAwareDtoInterface;
use App\Exceptions\ExternalApiPreconditionException;
use App\Exceptions\ExternalApiTemporaryException;
use App\Exceptions\ExternalApiViolationException;
use App\Exceptions\SomethingWrongException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class ErrorHandlingCall
{
    public function __invoke(HttpAwareDtoInterface $dto, \Closure $next)
    {
        try {
            $response = $next($dto);

            return $response;
        } catch (RequestException $e) {
            if ($e->response->clientError()) {
                throw new ExternalApiViolationException("External error while fetching bestseller data.", $e);
            }
            throw new ExternalApiTemporaryException("Bestseller data unavailable.", $e);
        } catch (ConnectionException $e) {
            throw new ExternalApiTemporaryException("Bestseller data unavailable.", $e);
        } catch (ExternalApiPreconditionException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new SomethingWrongException("Failed to fetch bestseller data.", $e->getCode(), $e);
        }
    }
}
