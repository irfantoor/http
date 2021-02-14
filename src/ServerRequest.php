<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Cookie;
use IrfanTOOR\Http\Request;
use IrfanTOOR\Http\StreamFactory;
use IrfanTOOR\Http\UriFactory;
use IrfanTOOR\Http\UploadedFileFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ServerRequest -- A request sent by a client and received at the server
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array */
    protected $server;

    /** @var array [Cookie ...]*/
    protected $cookies;

    /** @var array */
    protected $get;

    /** @var array */
    protected $post;

    /** @var array [UploadedFile ...]*/
    protected $files;

    /** @var array */
    protected $attributes;

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

        $this->server = array_merge(
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
            $init['server'] ?? [],
        );

        # method
        $init['method'] = $init['method'] ?? $this->server['REQUEST_METHOD'];

        # uri
        $init['uri'] = $init['uri'] ?? UriFactory::createFromEnvironment($this->server);

        # Headers from $_SERVER => the keys starting with HTTP_
        $headers = [];

        foreach($this->server as $k => $v) {
            $k = strtoupper($k);

            if (strpos($k, 'HTTP_') === 0) {
                $k = substr($k, 5);
            } else {
                if (!isset(self::$special[$k]))
                    continue;
            }

            // normalize key
            $k = str_replace(
                ' ',
                '-',
                ucwords(strtolower(str_replace('_', ' ', $k)))
            );

            $headers[$k] = $v;
        }

        $init['headers'] = array_merge($headers, $init['headers'] ?? []);

        parent::__construct($init);

        # Process $_COOKIE
        $cookies = array_merge($_COOKIE, $init['cookies'] ?? []);
        $this->cookies = [];

        foreach ($cookies as $k => $v) {
            $this->cookies[] = new Cookie([
                'name' => $k,
                'value' => $v,
            ]);
        }

        # process $_GET
        $this->get  = array_merge($_GET, $init['get'] ?? []);

        # process $_POST
        $this->post = array_merge($_POST, $init['post'] ?? []);

        # Process $_FILES
        $this->files = [];
        $files = array_merge($_FILES, $init['files'] ?? []);

        if ($files !== []) {
            foreach ($files as $id => $file) {
                if (!isset($file['error']))
                    continue;

                $this->files = [];

                if (!is_array($file['error'])) {
                    $file['file'] = $file['tmp_name'];
                    $this->files[$id] = new UploadedFile($file);
                } else {
                    $data = [];

                    foreach ($file['error'] as $sid => $error) {
                        $data['file']  = $file['tmp_name'][$sid];
                        $data['name']  = $file['name'][$sid] ?? null;
                        $data['type']  = $file['type'][$sid] ?? null;
                        $data['size']  = $file['size'][$sid] ?? 0;
                        $data['error'] = $file['error'][$sid] ?? null;

                        $this->files[$sid] = new UploadedFile($data);
                    }
                }
            }
        }

        # Process attributes
        $this->attributes = $init['attributes'] ?? [];
    }

    public function getServerParams()
    {
        return $this->server;
    }

    public function getCookieParams()
    {
        return $this->cookies;
    }

    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;

        foreach ($cookies as $k => $v) {
            $clone->cookies[] = new Cookie([
                'name' => $k,
                'value' => $v,
            ]);
        }

        return $clone;
    }

    public function getQueryParams()
    {
        return $this->get;
    }

    public function withQueryParams(array $query)
    {
        return $this->clone(['get' => $query]);
    }

    public function getUploadedFiles()
    {
        return $this->files;
    }

    public function withUploadedFiles(array $files)
    {
        $clone = clone $this;

        foreach ($files as $file) {
            $clone->files[] = new UploadedFile($file);
        }

        return $clone;
    }

    public function getParsedBody()
    {
        return $this->post;
    }

    public function withParsedBody($data)
    {
        return $this->clone(['post' => $data]);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return
            array_key_exists($name, $this->attributes)
            ? $this->attributes[$name]
            : $default
        ;
    }

    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
