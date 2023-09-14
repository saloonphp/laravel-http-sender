<?php

declare(strict_types=1);

namespace Saloon\HttpSender;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

class HttpPendingRequest extends PendingRequest
{
    /**
     * Constructor
     */
    public function __construct(Factory $factory = null)
    {
        parent::__construct($factory);

        $this->options = [];
    }
}
