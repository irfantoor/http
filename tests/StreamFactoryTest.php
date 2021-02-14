<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\Stream;
use IrfanTOOR\Http\StreamFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

class StreamFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new StreamFactory();

        $this->assertInstanceOf(StreamFactory::class, $factory);
        $this->assertImplements(StreamFactoryInterface::class, $factory);
    }

    function testCreateStream()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('hello world!');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertImplements(StreamInterface::class, $stream);
        $this->assertEquals('hello world!', (string) $stream);
    }

    function testCreateStreamFromFile()
    {
        file_put_contents('test.txt', 'hello world! from test.txt');
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile('test.txt', 'r');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertImplements(StreamInterface::class, $stream);
        $this->assertEquals('hello world! from test.txt', (string) $stream);

        unlink('test.txt');
    }

    function testCreateStreamFromResource()
    {
        file_put_contents('test.txt', 'hello world! from test.txt');
        $fp = fopen('test.txt', 'r');
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromResource($fp);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertImplements(StreamInterface::class, $stream);
        $this->assertEquals('hello world! from test.txt', (string) $stream);

        unlink('test.txt');
    }
}
