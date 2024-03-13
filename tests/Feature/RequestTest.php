<?php

declare(strict_types=1);

use Saloon\HttpSender\HttpSender;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;
use Saloon\HttpSender\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;
use Saloon\HttpSender\Tests\Fixtures\Connectors\InvalidConnectionConnector;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = HttpSenderConnector::make()->send($request);

    expect($response->getConnector()->sender())->toBeInstanceOf(HttpSender::class);

    $data = $response->json();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $response = HttpSenderConnector::make()->send($request);

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

test('a request will throw an exception if a connection error happens', function () {
    $request = new UserRequest;
    $connector = new InvalidConnectionConnector;

    $this->expectException(FatalRequestException::class);
    $this->expectExceptionMessage('Could not resolve host: invalid.saloon.dev');

    $connector->send($request);
});
