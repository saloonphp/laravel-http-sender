<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('default guzzle config options are sent', function () {
    $connector = new HttpSenderConnector();

    $connector->sender()->addMiddleware(function (callable $handler) use ($connector) {
        return function (RequestInterface $guzzleRequest, array $options) use ($connector) {
            expect($options)->toHaveKey('http_errors', false);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);

            $factoryCollection = $connector->sender()->getFactoryCollection();

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factoryCollection->responseFactory, $factoryCollection->streamFactory));
        };
    });

    $connector->send(new UserRequest);
});

test('you can pass additional guzzle config options and they are merged from the connector and request', function () {
    $connector = new HttpSenderConnector();

    $connector->config()->add('debug', true);

    $connector->sender()->addMiddleware(function (callable $handler) use ($connector) {
        return function (RequestInterface $guzzleRequest, array $options) use ($connector) {
            expect($options)->toHaveKey('http_errors', false);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);
            expect($options)->toHaveKey('debug', true);
            expect($options)->toHaveKey('verify', false);

            $factoryCollection = $connector->sender()->getFactoryCollection();

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factoryCollection->responseFactory, $factoryCollection->streamFactory));
        };
    });

    $request = new UserRequest;

    $request->config()->add('verify', false);

    $connector->send($request);
});

test('you can pass additional headers that will be merged with the default headers from the psr request', function () {
    $connector = new HttpSenderConnector();

    $connector->config()->add('debug', true);

    $connector->sender()->addMiddleware(function (callable $handler) use ($connector) {
        return function (RequestInterface $guzzleRequest, array $options) use ($connector) {
            expect($guzzleRequest->getHeaders())->toBe([
                'Host' => ['tests.saloon.dev'],
                'Accept' => ['application/json'],
                'User-Agent' => ['Saloon'],
            ]);

            $factoryCollection = $connector->sender()->getFactoryCollection();

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factoryCollection->responseFactory, $factoryCollection->streamFactory));
        };
    });

    $request = new UserRequest;

    $request->config()->add('headers', ['User-Agent' => 'Saloon']);

    $connector->send($request);
});
