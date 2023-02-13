<?php

namespace Saloon\HttpSender\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UserRequest extends Request
{
    /**
     * HTTP Method
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method = Method::GET;

    /**
     * Resolve the endpoint
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
