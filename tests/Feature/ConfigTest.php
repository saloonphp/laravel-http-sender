<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('default guzzle config options are sent', function () {
    $connector = new HttpSenderConnector();

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $guzzleRequest, array $options) {
            expect($options)->toHaveKey('http_errors', false);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send(new UserRequest);
});

test('you can pass additional guzzle config options and they are merged from the connector and request', function () {
    $connector = new HttpSenderConnector();

    $connector->config()->add('debug', true);

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $guzzleRequest, array $options) {
            expect($options)->toHaveKey('http_errors', false);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);
            expect($options)->toHaveKey('debug', true);
            expect($options)->toHaveKey('verify', false);

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $request = new UserRequest;

    $request->config()->add('verify', false);

    $connector->send($request);
});

test('the withBasicAuth method can be used to add the auth to the guzzle config', function () {
    $connector = new HttpSenderConnector();

    $connector->withBasicAuth('Sammyjo20', 'Yeehaw');

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $guzzleRequest, array $options) {
            expect($options)->toHaveKey('auth', ['Sammyjo20', 'Yeehaw']);

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send(new UserRequest);
});
