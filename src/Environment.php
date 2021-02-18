<?php

/**
 * IrfanTOOR\Http\Environment
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR\Http;

use IrfanTOOR\Collection;

/**
 * Environment
 */
class Environment extends Collection
{
    /**
     * Constructs a cookie from provided key, value pair(s) and options
     */
    public function __construct(array $init = [])
    {
        # Process $_SERVER and environment
        $https =
            (($init['HTTPS'] ?? 'off') !== 'off')
            || (($init['REQUEST_SCHEME'] ?? 'http') === 'https')
        ;

        if ($https) {
            $defscheme = 'https';
            $defport = 443;
        } else {
            $defscheme = 'http';
            $defport = 80;
        }

        $init = array_merge(
            [
                'SERVER_PROTOCOL'      => "HTTP/1.1",
                'REQUEST_METHOD'       => "GET",
                'REQUEST_SCHEME'       => $defscheme,
                'SCRIPT_NAME'          => "",
                'REQUEST_URI'          => "",
                'QUERY_STRING'         => "",
                'SERVER_NAME'          => "localhost",
                'SERVER_PORT'          => $defport,
                'HTTP_HOST'            => "localhost",
                'HTTP_ACCEPT'          => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                'HTTP_ACCEPT_LANGUAGE' => "en-US,en;q=0.8",
                'HTTP_ACCEPT_CHARSET'  => "ISO-8859-1,utf-8;q=0.7,*;q=0.3",
                'HTTP_USER_AGENT'      => "Irfan's Engine",
                'REMOTE_ADDR'          => "127.0.0.1",
                'REQUEST_TIME'         => time(),
                'REQUEST_TIME_FLOAT'   => microtime(true),
            ],
            $_SERVER,

            # env is merged here for simplicity as ServerRequestInterface offers only
            # getServerParams.
            getenv(),
            $init,
        );

        parent::__construct($init);
    }
}
