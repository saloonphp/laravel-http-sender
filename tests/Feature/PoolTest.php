<?php

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Response;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\PendingRequest;
use Saloon\HttpSender\HttpSender;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;
use Saloon\HttpSender\Tests\Fixtures\Connectors\InvalidConnectionConnector;
use Saloon\HttpSender\Tests\Fixtures\Requests\UserRequest;

test('you can create a pool on a connector', function () {
    $connector = new HttpSenderConnector;
    $count = 0;

    expect($connector->sender())->toBeInstanceOf(HttpSender::class);

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ]);

    $pool->setConcurrency(5);

    $pool->withResponseHandler(function (Response $response) use (&$count) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ]);

        $count++;
    });

    $promise = $pool->send();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $promise->wait();

    expect($count)->toEqual(5);
});

test('if a pool has a request that cannot connect it will be caught in the handleException callback', function () {
    $connector = new InvalidConnectionConnector;
    $count = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ]);

    $pool->setConcurrency(5);

    $pool->withExceptionHandler(function (FatalRequestException $ex) use (&$count) {
        expect($ex)->toBeInstanceOf(FatalRequestException::class);
        expect($ex->getPrevious())->toBeInstanceOf(ConnectException::class);
        expect($ex->getPendingRequest())->toBeInstanceOf(PendingRequest::class);

        $count++;
    });

    $promise = $pool->send();

    $promise->wait();

    expect($count)->toEqual(5);
});
