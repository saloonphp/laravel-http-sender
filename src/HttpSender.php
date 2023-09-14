<?php

declare(strict_types=1);

namespace Saloon\HttpSender;

use Throwable;
use Saloon\Http\Response;
use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use Illuminate\Http\Client\Factory;
use Saloon\Repositories\ArrayStore;
use Saloon\Http\Senders\GuzzleSender;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as HttpResponse;
use Saloon\Exceptions\Request\FatalRequestException;
use Illuminate\Http\Client\RequestException as HttpRequestException;

class HttpSender extends GuzzleSender
{
    /**
     * Guzzle middleware used to handle Laravel's Pending Request.
     */
    protected LaravelMiddleware $laravelMiddleware;

    /**
     * Create the HTTP client.
     */
    public function __construct()
    {
        parent::__construct();

        $this->handlerStack->push(
            $this->laravelMiddleware = new LaravelMiddleware
        );
    }

    /**
     * Send the request synchronously
     *
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     */
    public function send(PendingRequest $pendingRequest): Response
    {
        $psrRequest = $pendingRequest->createPsrRequest();

        try {
            $laravelPendingRequest = $this->createLaravelPendingRequest($psrRequest, false);

            $response = $laravelPendingRequest->send(
                $pendingRequest->getMethod()->value,
                (string)$psrRequest->getUri(),
                $this->createRequestOptions($pendingRequest, $psrRequest),
            );
        } catch (ConnectionException|ConnectException $exception) {
            throw new FatalRequestException($exception, $pendingRequest);
        }

        // When the response is a normal HTTP Client Response, we can create the response

        return $this->createResponse($response->toPsrResponse(), $pendingRequest, $psrRequest, $response->toException());
    }

    /**
     * Send the request asynchronously
     */
    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface
    {
        $psrRequest = $pendingRequest->createPsrRequest();

        $laravelPendingRequest = $this->createLaravelPendingRequest($psrRequest, true);

        // Create the promise.

        /** @var PromiseInterface $promise */
        $promise = $laravelPendingRequest->send(
            $pendingRequest->getMethod()->value,
            (string)$psrRequest->getUri(),
            $this->createRequestOptions($pendingRequest, $psrRequest),
        );

        // Send the request

        return $this->processPromise($psrRequest, $promise, $pendingRequest);
    }

    /**
     * Update the promise provided by Guzzle.
     */
    protected function processPromise(RequestInterface $psrRequest, PromiseInterface $promise, PendingRequest $pendingRequest): PromiseInterface
    {
        // When it comes to promises, it's a little tricky because of Laravel's built-in
        // exception handler which always converts a request exception into a response.
        // Here we will undo that functionality by catching the exception and throwing
        // it back down the "otherwise"/"catch" chain

        return $promise
            ->then(function (HttpResponse|TransferException $result) {
                $exception = $result instanceof TransferException ? $result : $result->toException();

                if ($exception instanceof Throwable) {
                    throw $exception;
                }

                return $result;
            })
            ->then(
                function (HttpResponse $response) use ($psrRequest, $pendingRequest) {
                    return $this->createResponse($response->toPsrResponse(), $pendingRequest, $psrRequest);
                },
            )
            ->otherwise(
                function (HttpRequestException|TransferException $exception) use ($pendingRequest, $psrRequest) {
                    // When the exception wasn't a HttpRequestException, we'll throw a fatal
                    // exception as this is likely a ConnectException, but it will
                    // catch any new ones Guzzle release.

                    if (! $exception instanceof HttpRequestException) {
                        throw new FatalRequestException($exception, $pendingRequest);
                    }

                    // Otherwise we'll create a response to convert into an exception.
                    // This will run the exception through the exception handlers
                    // which allows the user to handle their own exceptions.

                    $response = $this->createResponse($exception->response->toPsrResponse(), $pendingRequest, $psrRequest, $exception);

                    // Throw the exception our way

                    throw $response->toException();
                }
            );
    }

    /**
     * Create the request options
     *
     * @return array<string, mixed>
     */
    protected function createRequestOptions(PendingRequest $pendingRequest, RequestInterface $psrRequest): array
    {
        $config = new ArrayStore($pendingRequest->config()->all());

        // We need to let Laravel catch and handle HTTP errors to preserve
        // the default behavior. It does so by inspecting the status code
        // instead of catching an exception which is what Saloon does.

        $config->add(RequestOptions::HTTP_ERRORS, false);

        // Now we'll add the headers - this is because Laravel doesn't
        // deal with PSR requests. We'll add the headers from the PSR
        // request to ensure the lowest-level values possible.

        $config->add(RequestOptions::HEADERS, $psrRequest->getHeaders());

        return $config->all();
    }

    /**
     * Create the Laravel Pending Request
     */
    protected function createLaravelPendingRequest(RequestInterface $psrRequest, bool $asynchronous): HttpPendingRequest
    {
        $httpPendingRequest = new HttpPendingRequest(resolve(Factory::class));
        $httpPendingRequest->setClient($this->client);

        $this->laravelMiddleware->setRequest($httpPendingRequest);

        if ($asynchronous === true) {
            $httpPendingRequest->async();
        }

        // We'll set the body format as "body" and provide the PSR body stream.
        // This means we can keep the efficient memory stream.

        $httpPendingRequest->bodyFormat('body')->setPendingBody($psrRequest->getBody());

        return $httpPendingRequest;
    }
}
