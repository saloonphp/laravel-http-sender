<?php

declare(strict_types=1);

namespace Saloon\HttpSender\Tests\Fixtures\Responses;

use Saloon\Http\Response;

class UserResponse extends Response
{
    /**
     * @return \Saloon\HttpSender\Tests\Fixtures\Responses\UserData
     * @throws \JsonException
     */
    public function customCastMethod(): UserData
    {
        return new UserData($this->json('foo'));
    }

    /**
     * @return string|null
     * @throws \JsonException
     */
    public function foo(): ?string
    {
        return $this->json('foo');
    }
}
