<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\Uri;
use IrfanTOOR\Http\UriFactory;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;

class UriFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new UriFactory();

        $this->assertInstanceOf(UriFactory::class, $factory);
        $this->assertImplements(UriFactoryInterface::class, $factory);
    }

    function testCreateUri()
    {
        $factory = new UriFactory();
        $uri = $factory->createUri('http://example.com');

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertImplements(UriInterface::class, $uri);
        # note the last / (put to comply)
        $this->assertEquals('http://example.com/', $uri->__toString());
    }
}
