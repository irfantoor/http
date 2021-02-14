<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\Request;
use IrfanTOOR\Http\Uri;
use IrfanTOOR\Http\RequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

class RequestFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new RequestFactory();

        $this->assertInstanceOf(RequestFactory::class, $factory);
        $this->assertImplements(RequestFactoryInterface::class, $factory);
    }

    function testCreateRequest()
    {
        $factory = new RequestFactory();

        # with a uri => string
        $request = $factory->createRequest('GET', 'http://example.com');
        $this->assertInstanceOf(Request::class, $request);
        $this->assertImplements(RequestInterface::class, $request);
        $this->assertInstanceOf(UriInterface::class, $request->getUri());

        # with a uri => UriInterface
        $uri = new Uri('http://example.com');
        $request = $factory->createRequest('GET', $uri);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertImplements(RequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals('http://example.com/', (string) $request->getUri());
    }
}
