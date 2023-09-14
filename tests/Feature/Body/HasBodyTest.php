<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\HttpSender\Tests\Fixtures\Requests\HasBodyRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the default body is loaded', function () {
    $request = new HasBodyRequest();

    expect($request->body()->get())->toEqual('name: Sam');
});

test('the http sender properly sends it', function () {
    $connector = new HttpSenderConnector;
    $request = new HasBodyRequest;

    $request->headers()->add('Content-Type', 'application/custom');

    $connector->sender()->addMiddleware(function (callable $handler) use ($connector, $request) {
        return function (RequestInterface $psrRequest, array $options) use ($connector, $request) {
            expect($psrRequest->getHeader('Content-Type'))->toEqual(['application/custom']);
            expect((string)$psrRequest->getBody())->toEqual((string)$request->body());

            $factoryCollection = $connector->sender()->getFactoryCollection();

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factoryCollection->responseFactory, $factoryCollection->streamFactory));
        };
    });

    $connector->send($request);
});
