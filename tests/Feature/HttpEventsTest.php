<?php

declare(strict_types=1);

use Saloon\Contracts\Response;
use Saloon\HttpSender\HttpSender;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Saloon\Exceptions\Request\RequestException;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the http events are fired when using the http sender', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new HttpSenderConnector;

    expect($connector->sender())->toBeInstanceOf(HttpSender::class);

    $responseA = $connector->send(new UserRequest);
    $responseB = $connector->send(new UserRequest);
    $responseC = $connector->send(new ErrorRequest);

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);
    expect($responseC->status())->toBe(500);

    Event::assertDispatched(RequestSending::class, 3);
    Event::assertDispatched(ResponseReceived::class, 3);
});

test('the http events are fired when using the http sender with asynchronous events', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new HttpSenderConnector;

    expect($connector->sender())->toBeInstanceOf(HttpSender::class);

    $responseA = $connector->sendAsync(new UserRequest)->wait();
    $responseB = $connector->sendAsync(new UserRequest)->wait();

    try {
        $connector->sendAsync(new ErrorRequest)->wait();
    } catch (RequestException $requestException) {
        $responseC = $requestException->getResponse();
    }

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);
    expect($responseC->status())->toBe(500);

    Event::assertDispatched(RequestSending::class, 3);
    Event::assertDispatched(ResponseReceived::class, 3);
});

test('the http events are fired when using request pools', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new HttpSenderConnector;

    expect($connector->sender())->toBeInstanceOf(HttpSender::class);

    $pool = $connector->pool([
        'a' => new UserRequest,
        'b' => new UserRequest,
        'c' => new ErrorRequest,
    ]);

    $responses = [];

    $pool->withResponseHandler(function (Response $response, string $key) use (&$responses) {
        $responses[$key] = $response;
    });

    $pool->withExceptionHandler(function (RequestException $requestException, string $key) use (&$responses) {
        $responses[$key] = $requestException->getResponse();
    });

    $pool->send()->wait();

    expect($responses['a']->status())->toBe(200);
    expect($responses['b']->status())->toBe(200);
    expect($responses['c']->status())->toBe(500);

    Event::assertDispatched(RequestSending::class, 3);
    Event::assertDispatched(ResponseReceived::class, 3);
});
