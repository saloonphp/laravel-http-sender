<?php

declare(strict_types=1);

namespace Saloon\HttpSender\Tests\Fixtures\Responses;

class UserData
{
    /**
     * CustomResponse constructor.
     */
    public function __construct(
        public string $foo
    ) {
        // ..
    }
}
