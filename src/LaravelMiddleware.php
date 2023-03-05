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
     *
     * @var \Illuminate\Http\Client\PendingRequest
     */
    protected PendingRequest $pendingRequest;

    /**
     * Invoke the middleware.
     *
     * @param callable $handler
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function ($request, $options) use ($handler): ResponseInterface|PromiseInterface {
            return $this->pendingRequest->pushHandlers(new HandlerStack($handler))->__invoke($request, $options);
        };
    }

    /**
     * Set the current pending request.
     *
     * @param \Illuminate\Http\Client\PendingRequest $pendingRequest
     * @return void
     */
    public function setRequest(PendingRequest $pendingRequest): void
    {
        $this->pendingRequest = $pendingRequest;
    }
}
