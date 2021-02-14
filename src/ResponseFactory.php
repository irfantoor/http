<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Create a new response.
 */
class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response(
            [
                'status_code'   => $code,
                'reason_phrase' => $reasonPhrase,
            ]
        );
    }
}
