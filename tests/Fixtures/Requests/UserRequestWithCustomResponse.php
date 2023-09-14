<?php

declare(strict_types=1);

namespace Saloon\HttpSender\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\HttpSender\Tests\Fixtures\Responses\UserResponse;

class UserRequestWithCustomResponse extends Request
{
    /**
     * HTTP Method
     */
    protected Method $method = Method::GET;

    /**
     * Custom response
     */
    protected ?string $response = UserResponse::class;

    /**
     * Resolve the endpoint
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
