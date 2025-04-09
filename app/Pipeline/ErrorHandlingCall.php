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
            // Client errors means there is a risk we are doing something wrong.
            if ($e->response->clientError()) {
                throw new ExternalApiViolationException('External error while fetching bestseller data.', $e);
            }
            // Server errors means we may retry it in near future.
            throw new ExternalApiTemporaryException('Bestseller data unavailable, retry in 5 minutes.', $e);
        } catch (ConnectionException $e) {
            // Timeouts, wrong dns, other network related stuff. Retrying immediately would probably still fail.
            throw new ExternalApiTemporaryException('Bestseller data unavailable, retry in an hour.', $e);
        } catch (ExternalApiPreconditionException $e) {
            // Proxy response schema validation exceptions, as these have proper report and render methods.
            throw $e;
        } catch (\Exception $e) {
            // All other exceptions.
            throw new SomethingWrongException('Failed to fetch bestseller data.', $e->getCode(), $e);
        }
    }
}
