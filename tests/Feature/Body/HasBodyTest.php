<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\HttpSender\Tests\Fixtures\Requests\HasBodyRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the default body is loaded', function () {
    $request = new HasBodyRequest();

    expect($request->body()->all())->toEqual('name: Sam');
});

test('the guzzle sender properly sends it', function () {
    $connector = new HttpSenderConnector;
    $request = new HasBodyRequest;

    $request->headers()->add('Content-Type', 'application/custom');

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/custom']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});
