<?php

namespace IrfanTOOR\Http;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/**
 * An object representing a URI
 */
class Uri implements UriInterface
{
    protected $scheme;
    protected $user;
    protected $pass;
    protected $host;
    protected $path;
    protected $port;
    protected $query;
    protected $fragment;

    public function __construct($url = null)
    {
        $this->scheme   = "";
        $this->user     = "";
        $this->pass     = "";
        $this->host     = "";
        $this->host     = "";
        $this->port     = null;
        $this->query    = "";
        $this->fragment = "";

        if ($url) {
            if (is_array($url))
                $this->parseArray($url);
            elseif(is_string($url))
                $this->parseString($url);
        }
    }

    protected function parseArray(array $url)
    {
        $keys = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'];

        foreach ($url as $k => $v) {
            if (in_array($k, $keys))
                $this->$k = $v;
        }
    }

    protected function parseString(string $url)
    {
        $parsed = parse_url($url);

        if (!$parsed)
            throw new InvalidArgumentException("Provided url is not a valid string");

        foreach ($parsed as $k => $v)
            $this->$k = $v;
    }

    protected function clone(array $data)
    {
        $clone = clone $this;

        foreach ($data as $key => $value)
            $clone->$key = $value;

        return $clone;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getAuthority()
    {
        $user_info = $this->getUserInfo();

        if ($user_info !== "")
            $user_info .= "@";

        $port = $this->getPort();
        $port = $port ? ":" . $port : "";

        return $user_info . $this->getHost() . $port;
    }

    public function getUserInfo()
    {
        return
            $this->user
            . (
                $this->pass
                ? ":" . $this->pass
                : ""
            )
        ;
    }

    public function getHost()
    {
        return $this->host ?? "";
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path ?? "";
    }

    public function getQuery()
    {
        return $this->query ?? "";
    }

    public function getFragment()
    {
        return $this->fragment ?? "";
    }

    public function withScheme($scheme)
    {
        return $this->clone(['scheme' => $scheme]);
    }

    public function withUserInfo($user, $pass = null)
    {
        return $this->clone(['user' => $user, 'pass' => $pass]);
    }

    public function withHost($host)
    {
        return $this->clone(['host' => $host]);
    }

    public function withPort($port)
    {
        return $this->clone(['port' => $port]);
    }

    public function withPath($path)
    {
        return $this->clone(['path' => $path]);
    }

    public function withQuery($query)
    {
        return $this->clone(['query' => $query]);
    }

    public function withFragment($fragment)
    {
        return $this->clone(['fragment' => $fragment]);
    }

    public function __toString()
    {
        try {
            $path = $this->getPath();
            $authority = $this->getAuthority();

            if ($authority !== ""){
                if ($path === '' || $path['0'] !== '/')
                    $path = '/' . $path;
            } else {
                if ($path !== '' && $path['0'] === '/')
                    $path = '/' . ltrim($path, '/');
            }

            return
                ($this->scheme ? $this->scheme . ':' : '') .
                ($authority ? "//" . $authority : "") .
                $path .
                ($this->query ? '?' . $this->query : '') .
                ($this->fragment ? '#' . $this->fragment : '');
        } catch(\Throwable $e) {
            return "";
        }
    }
}
