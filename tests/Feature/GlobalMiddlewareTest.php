<?php

declare(strict_types=1);

use Saloon\HttpSender\HttpSender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the global middleware of the http client factory is also applied to saloon requests', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    $globalMiddlewareWasCalled = false;

    Http::globalRequestMiddleware(function ($request) use (&$globalMiddlewareWasCalled) {
        $globalMiddlewareWasCalled = true;

        return $request;
    });

    $connector = new HttpSenderConnector;
    $connector->send(new UserRequest);

    expect($globalMiddlewareWasCalled)->toBeTrue();
});
