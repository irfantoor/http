<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Uri;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;

class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }

    public static function createFromEnvironment($env): UriInterface
    {
        # scheme
        $is_https =
            (($env['HTTPS'] ?? 'off') !== 'off')
            || (($env['REQUEST_SCHEME'] ?? 'http') === 'https')
        ;

        $scheme = $is_https ? "https" : "http";
        $host   = $env['SERVER_NAME'] ?? "localhost";
        $port   = $env['SERVER_PORT'] ?? "";


        if (
            ($scheme === 'https' && $port === 443)
            || ($scheme === 'http' && $port === 80)
        )
            $port = "";
        else
            $port = ":" . $port;

        $path   = $env['REQUEST_URI'];

        return new Uri($scheme . "://" . $host . $port . $path);
    }
}
