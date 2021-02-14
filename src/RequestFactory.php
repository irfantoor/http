<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Create a new request.
 */
class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request(
            [
                'method' => $method,
                'uri'    => $uri
            ]
        );
    }
}
