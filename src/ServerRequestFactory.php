<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

/**
 * Create a new server request.
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest(
            [
                'method' => $method,
                'uri'    => $uri,
                'server' => $serverParams
            ]
        );
    }

    public function createFromEnvironment(array $init = []): ServerRequestInterface
    {
        return new ServerRequest($init);
    }
}
