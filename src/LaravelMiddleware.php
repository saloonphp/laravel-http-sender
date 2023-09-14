<?php

declare(strict_types=1);

namespace Saloon\HttpSender;

use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;

class LaravelMiddleware
{
    /**
     * The current pending request.
     */
    protected PendingRequest $pendingRequest;

    /**
     * Invoke the middleware.
     */
    public function __invoke(callable $handler): callable
    {
        return function ($request, $options) use ($handler): ResponseInterface|PromiseInterface {
            return $this->pendingRequest->pushHandlers(new HandlerStack($handler))->__invoke($request, $options);
        };
    }

    /**
     * Set the current pending request.
     */
    public function setRequest(PendingRequest $pendingRequest): void
    {
        $this->pendingRequest = $pendingRequest;
    }
}
