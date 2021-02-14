<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\{Uri, Message};
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\{RequestInterface, UriInterface};
use ReflectionClass;

/**
 * Request -- An outgoing, client-side request.
 */
class Request extends Message implements RequestMethodInterface, RequestInterface
{
    /** @var array */
    protected static $methods;

    /** @var string */
    protected $method;

    /** @var UriInterface */
    protected $uri;

    public function __construct(array $init = [])
    {
        parent::__construct($init);

        # convert all the constants from RequestMethodInterface to methods
        if (!self::$methods) {
            $rc = new ReflectionClass(__CLASS__);
            $constants = $rc->getConstants();

            foreach ($constants as $k => $v)
                self::$methods[] = $v;
        }

        # method
        $this->method = $init['method'] ?? "GET";

        # uri
        $uri = $init['uri'] ?? "/";

        if (is_string($uri))
            $this->uri = new Uri($uri);
        elseif(is_a($uri, UriInterface::class))
            $this->uri = $uri;
        else
            throw new \InvalidArgumentException("Invalid init value passed for 'uri'.");

        $uri_host = $this->uri->getHost();

        if (!$this->hasHeader('host') && $uri_host!== "")
            $this->setHeader('Host', $uri_host);
    }

    protected function __clone()
    {
        parent::__clone();
        $this->uri = clone $this->uri;
    }

    public function getMethods()
    {
        return self::$methods;
    }

    public function getRequestTarget()
    {
        return (string) $this->uri;
    }

    public function withRequestTarget($requestTarget)
    {
        return $this->clone(['uri' => new Uri($requestTarget)]);
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        return $this->clone(['method' => $method]);
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = $this->clone(['uri' => $uri]);
        $host = $uri->getHost();

        if (!$preserveHost) {
            if ($host)
                $clone->setHeader('Host', $host);
        } else {
            if (!$clone->hasHeader('host') || $clone->getHeader('host') === "")
                if ($host)
                    $clone->setHeader('Host', $host);
        }

        return $clone;
    }
}
