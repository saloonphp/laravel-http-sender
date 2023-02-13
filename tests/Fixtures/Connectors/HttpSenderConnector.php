<?php

namespace Saloon\HttpSender\Tests\Fixtures\Connectors;

use Saloon\Contracts\Sender;
use Saloon\Http\Connector;
use Saloon\HttpSender\Http\Senders\HttpSender;
use Saloon\Traits\Plugins\AcceptsJson;

class HttpSenderConnector extends Connector
{
    use AcceptsJson;

    /**
     * Resolve the Base URL
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://tests.saloon.dev/api';
    }

    /**
     * Define the default sender
     *
     * @return \Saloon\Contracts\Sender
     */
    protected function defaultSender(): Sender
    {
        return new HttpSender();
    }
}
