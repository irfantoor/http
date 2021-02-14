<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP messages implements the common interface of a request and a response
 */
class Message implements MessageInterface
{
    /** @var string */
    protected $version;

    /** @var array */
    protected $headers = [];

    /** @var StreamInterface */
    protected $body;

    /**
     * Message constructor
     *
     * @param array $init Initialization array
     */
    public function __construct(array $init = [])
    {
        # version
        $this->version = $init['version'] ?? "1.1";

        # headers
        foreach (($init['headers'] ?? []) as $name => $value)
            $this->setHeader($name, $value);

        # body
        $this->body = new Stream($init['body'] ?? "");
    }

    protected function __clone()
    {
        $this->body = clone $this->body;
    }

    protected function clone(array $data)
    {
        $clone = clone $this;

        foreach ($data as $key => $value)
            $clone->$key = $value;

        return $clone;
    }

    protected function setHeader(string $name, $value)
    {
        if (!is_array($value))
            $value = [$value];

        $this->headers[strtolower($name)] = [$name, $value];
    }

    public function getProtocolVersion()
    {
        return $this->version;
    }

    public function withProtocolVersion($version)
    {
        return $this->clone(['version' => $version]);
    }

    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers as $k => $v)
            $headers[$v[0]] = $v[1];

        return $headers;
    }

    public function hasHeader($name)
    {
        return array_key_exists(strtolower($name), $this->headers);
    }

    public function getHeader($name)
    {
        return $this->headers[strtolower($name)][1] ?? [];
    }

    public function getHeaderLine($name)
    {
        return implode(",", $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);
        return $clone;
    }

    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;

        if (!is_array($value))
            $value = [$value];

        $clone->setHeader($name, array_merge($clone->getHeader($name), $value));

        return $clone;
    }

    public function withoutHeader($name)
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        return $this->clone(['body' => $body]);
    }
}
