<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\ServerRequest;
use IrfanTOOR\Http\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new ServerRequestFactory();

        $this->assertInstanceOf(ServerRequestFactory::class, $factory);
        $this->assertImplements(ServerRequestFactoryInterface::class, $factory);
    }

    function testCreateServerRequest()
    {
        $factory = new ServerRequestFactory();

        $request = $factory->createServerRequest('GET', 'http://example.com/');
        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertImplements(ServerRequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals('http://example.com/', (string) $request->getUri());
    }

    function testCreateFromEnvironment()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createFromEnvironment();

        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertImplements(ServerRequestInterface::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals('http://localhost/', (string) $request->getUri());
    }
}
