<?php

declare(strict_types=1);

use Saloon\HttpSender\HttpSender;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the http events are fired when using the http sender', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new HttpSenderConnector;
    $responseA = $connector->send(new UserRequest);
    $responseB = $connector->send(new UserRequest);
    $responseC = $connector->send(new UserRequest);

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);
    expect($responseC->status())->toBe(200);

    Event::assertDispatched(RequestSending::class, 3);
    Event::assertDispatched(ResponseReceived::class, 3);
});

test('the http events are fired when using the http sender with asynchronous events', function () {
    Config::set('saloon.default_sender', HttpSender::class);

    Event::fake();

    $connector = new HttpSenderConnector;
    $responseA = $connector->sendAsync(new UserRequest)->wait();
    $responseB = $connector->sendAsync(new UserRequest)->wait();
    $responseC = $connector->sendAsync(new UserRequest)->wait();

    expect($responseA->status())->toBe(200);
    expect($responseB->status())->toBe(200);
    expect($responseC->status())->toBe(200);

    Event::assertDispatched(RequestSending::class, 3);
    Event::assertDispatched(ResponseReceived::class, 3);
});
