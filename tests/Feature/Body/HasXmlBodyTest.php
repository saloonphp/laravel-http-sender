<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\HttpSender\Tests\Fixtures\Requests\HasXmlBodyRequest;
use Saloon\HttpSender\Tests\Fixtures\Connectors\HttpSenderConnector;

test('the default body is loaded', function () {
    $request = new HasXmlBodyRequest();

    expect($request->body()->get())->toEqual('<p>Howdy</p>');
});

test('the content-type header is set in the pending request', function () {
    $request = new HasXmlBodyRequest();

    $pendingRequest = HttpSenderConnector::make()->createPendingRequest($request);

    expect($pendingRequest->headers()->all())->toHaveKey('Content-Type', 'application/xml');
});

test('the http sender properly sends it', function () {
    $connector = new HttpSenderConnector;
    $request = new HasXmlBodyRequest;

    $request->middleware()->onRequest(static function (PendingRequest $pendingRequest) {
        expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/xml');
    });

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/xml']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});
