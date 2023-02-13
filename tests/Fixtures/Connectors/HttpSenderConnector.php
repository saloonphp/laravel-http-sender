<?php

declare(strict_types=1);

namespace Saloon\HttpSender\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Sender;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\HttpSender\Http\Senders\HttpSender;

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
