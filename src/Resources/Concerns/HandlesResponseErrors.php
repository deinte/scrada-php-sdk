<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources\Concerns;

use Closure;
use Deinte\ScradaSdk\Exceptions\AuthenticationException;
use Deinte\ScradaSdk\Exceptions\NotFoundException;
use Deinte\ScradaSdk\Exceptions\ScradaException;
use Deinte\ScradaSdk\Exceptions\ValidationException;
use Saloon\Http\Response;

/**
 * Shared error handling helpers for resources.
 */
trait HandlesResponseErrors
{
    /**
     * @param  Closure():NotFoundException|null  $notFoundFactory
     */
    private function throwIfError(Response $response, ?Closure $notFoundFactory = null): void
    {
        if ($response->status() === 401) {
            throw AuthenticationException::invalidCredentials();
        }

        if ($response->status() === 404) {
            $factory = $notFoundFactory ?? static fn (): NotFoundException => NotFoundException::resource('Resource', 'unknown');

            throw $factory();
        }

        if ($response->status() === 422) {
            throw ValidationException::fromResponse($response);
        }

        if ($response->failed()) {
            throw ScradaException::fromResponse($response);
        }
    }
}
