<?php

declare(strict_types=1);

namespace Saloon\HttpSender;

use GuzzleHttp\HandlerStack;
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
     */
    public function __invoke($handler)
    {
        return function ($request, $options) use ($handler) {
            return $this->pendingRequest->pushHandlers(new HandlerStack($handler))->__invoke($request, $options);
        };
    }

    /**
     * Set the current pending request.
     */
    public function setRequest(PendingRequest $pendingRequest)
    {
        $this->pendingRequest = $pendingRequest;
    }
}
