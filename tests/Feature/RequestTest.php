¬<?php

use Saloon\HttpSender\Http\Senders\HttpSender;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;
use Saloon\HttpSender\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = HttpSenderConnector::make()->send($request);

    expect($response->getPendingRequest()->getSender())->toBeInstanceOf(HttpSender::class);

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
