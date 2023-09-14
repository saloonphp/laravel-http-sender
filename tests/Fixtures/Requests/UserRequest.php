<?php

declare(strict_types=1);

namespace Saloon\HttpSender\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UserRequest extends Request
{
    /**
     * HTTP Method
     */
    protected Method $method = Method::GET;

    /**
     * Resolve the endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
