<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\{Message, Stream, Uri};
use Psr\Http\Message\{
    MessageInterface,
    StreamInterface,
};

class MessageTest extends Test
{
    function testMessageInstance()
    {
        $m = new Message();

        $this->assertInstanceOf(Message::class, $m);
        $this->assertImplements(MessageInterface::class, $m);
        $this->assertImplements(StreamInterface::class, $m->getBody());
    }

    function testDefaultValues()
    {
        $m = new Message();

        $this->assertEquals('1.1', $m->getProtocolVersion());
        $this->assertEquals([], $m->getHeaders());
        $this->assertInstanceOf(Stream::class, $m->getBody());
        $this->assertEquals('', $m->getBody());
    }

    function testPassedValues()
    {
        $m = new Message(
            [
                'version' => '1.0',
                'headers' => [
                    'something' => 'Some Header',
                ],
                'body'    => 'Voila Voila!',
            ]
        );

        $this->assertEquals('1.0', $m->getProtocolVersion());
        $this->assertEquals(['something' => ['Some Header']], $m->getHeaders());
        $this->assertInstanceOf(Stream::class, $m->getBody());
        $this->assertEquals('Voila Voila!', $m->getBody());
    }

    function testProtocolVersion()
    {
        $m = new Message();
        $this->assertEquals("1.1", $m->getProtocolVersion());

        $m = $m->withProtocolVersion("2.0");
        $this->assertEquals("2.0", $m->getProtocolVersion());

        $m = new Message([
            'version' => "3.0"
        ]);
        $this->assertEquals("3.0", $m->getProtocolVersion());
    }

    function testGetHeaders()
    {
        $h = [
            'content-type' => 'plain/text',
            'engine'       => 'ie',
        ];

        $m = new Message(['headers' => $h]);
        $this->assertEquals(
            [
                'content-type' => ['plain/text'],
                'engine'       => ['ie'],
            ],
            $m->getHeaders()
        );

        $this->assertEquals(2, count($m->getHeaders()));
    }

    function testHasHeader()
    {
        $m = new Message();
        $m = $m
            ->withHeader("Content-Length", 100)
            ->withHeader("Content-Type", "text/plain")
            ->withHeader("Host", "example.com")
        ;

        $this->assertTrue($m->hasHeader('content-length'));
        $this->assertTrue($m->hasHeader('content-type'));
        $this->assertTrue($m->hasHeader('Host'));
        $this->assertFalse($m->hasHeader('user-agent'));
        $this->assertFalse($m->hasHeader('accept'));
    }

    function testGetHeader()
    {
        $m = new Message();

        $headers = [
            'Content-Length' => 100,
            'Content0Type' => 'text/plain',
            'Host' => 'example.com'
        ];

        foreach ($headers as $key => $value) {
            $m = $m->withHeader($key, $value);
        }

        foreach ($m->getHeaders() as $key => $value)
        {
            $lower = strtolower($key);
            $upper = strtoupper($key);

            $this->assertEquals($value, $m->getHeader($key));
            $this->assertEquals([$headers[$key]], $m->getHeader($key));

            $this->assertEquals($value, $m->getHeader($lower));
            $this->assertEquals([$headers[$key]], $m->getHeader($lower));

            $this->assertEquals($value, $m->getHeader($upper));
            $this->assertEquals([$headers[$key]], $m->getHeader($upper));
        }

        $this->assertEquals([], $m->getHeader("hello"));
    }

    function testGetHeaderLine()
    {
        $m = new Message(
            [
                'headers' => [
                    'Content-type' => 'plain/text',
                ]
            ]
        );

        $this->assertEquals('plain/text', $m->getHeaderLine('content-type'));

        $m2 = $m->withAddedHeader('Content-Type', 'Charset: utf8');
        $this->assertEquals('plain/text,Charset: utf8', $m2->getHeaderLine('content-type'));
        $this->assertEquals("", $m->getHeaderLine("hello"));
    }

    function testWithHeaders()
    {
        $m = new Message();
        $m2 = $m->withHeader('Content-Type', 'plain/text');

        # if no key is present an empty array is returned
        $this->assertEquals([], $m->getHeader('content-type'));

        # returns an array of defined values of a header
        $this->assertEquals(['plain/text'], $m2->getHeader('content-type'));

        # The real case of the key is preserved
        $headers = $m2->getHeaders();
        $this->assertTrue(array_key_exists('Content-Type', $headers));
        $this->assertFalse(array_key_exists('Content-type', $headers));

        $m3 = $m2->withHeader('Content-type', 'utf8');

        # immutibility
        $this->assertEquals(['plain/text'], $m2->getHeader('content-type'));
        $this->assertEquals(['utf8'], $m3->getHeader('content-type'));

        # immutibility
        $headers = $m2->getHeaders();
        $this->assertTrue(array_key_exists('Content-Type', $headers));
        $this->assertFalse(array_key_exists('Content-type', $headers));

        # key changes with the newer withHeader
        $headers = $m3->getHeaders();
        $this->assertFalse(array_key_exists('Content-Type', $headers));
        $this->assertTrue(array_key_exists('Content-type', $headers));
    }

    function testAddHeader()
    {
        $m = new Message();

        $m2 = $m->withAddedHeader('Content-Type', 'plain/text');
        $this->assertEquals(['plain/text'], $m2->getHeader('content-type'));

        $m3 = $m2->withAddedHeader('Content-Type', 'utf8');
        $this->assertEquals(['plain/text', 'utf8'], $m3->getHeader('content-type'));
    }

    function testWithoutHeader()
    {
        $m = new Message(
            [
                'headers' => [
                    'content-type' => 'plain/text',
                ]
            ]
        );

        $m2 = $m->withoutHeader('Content-Type');

        # immutability
        $this->assertEquals(['plain/text'], $m->getHeader('content-type'));

        # returns an empty array if the header does not exist
        $this->assertEquals([], $m2->getHeader('content-type'));
    }

    function testGetBody()
    {
        $m = new Message();
        $b = $m->getBody();

        $this->assertInstanceOf(Stream::class, $b);
        $this->assertImplements(StreamInterface::class, $b);
        $this->assertEquals("", (string) $b);

        $this->assertSame($b, $m->getBody());

        $m->getBody()->write('Hello');
        $this->assertEquals('Hello', $m->getBody());
        $m->getBody()->write(' ');
        $this->assertEquals('Hello ', $m->getBody());
        $m->getBody()->write('World!');
        $this->assertEquals('Hello World!', $m->getBody());
    }

    function testWithBody()
    {
        $m = new Message();
        $b = $m->getBody();
        $s = new Stream("Hello World!");

        $m2 = $m->withBody($s);
        $b2 = $m2->getBody();

        $this->assertSame($b, $m->getBody());
        $this->assertSame($b2, $m2->getBody());
        $this->assertNotSame($b, $b2);

        $this->assertEquals("", (string) $b);
        $this->assertEquals("Hello World!", (string) $b2);
    }
}
