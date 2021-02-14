<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\{
    Message,
    Request,
    Uri
};
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\{
    MessageInterface,
    RequestInterface,
    StreamInterface,
    UriInterface
};

class RequestTest extends Test
{
    function getRequest($init=[])
    {
        return new Request($init);
    }

    function testRequestInstance()
    {
        $request = $this->getRequest();

        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Message::class, $request);
        $this->assertImplements(RequestInterface::class, $request);
        $this->assertImplements(RequestMethodInterface::class, $request);
        $this->assertImplements(MessageInterface::class, $request);
    }

    function testDefaults()
    {
        $request = $this->getRequest();
        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertEquals([], $request->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('', (string) $request->getBody());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/', (string) $request->getUri());
    }

    function testRequestWithInit()
    {
        $url = 'https://example.com:8080/hello/world?something=here&somewhat=present';
        $r = $this->getRequest([
            'method' => 'POST',
            'uri' => $url,
        ]);

        $this->assertEquals('POST', $r->getMethod());
        $uri = $r->getUri();

        $this->assertEquals($url, (string) $uri);
        $this->assertEquals($url, $uri->__toString());
    }

    function testGetMethods()
    {
        $request = $this->getRequest();

        foreach ($request->getMethods() as $method)
        {
            eval('$const = ' . Request::class . '::METHOD_' . $method . ';');
            $this->assertEquals($const, $method);
        }
    }

    function testGetRequestTarget()
    {
        $r = new Request();
        $this->assertEquals("/", $r->getRequestTarget());

        # from string
        $url = 'my-scheme://test.mydomain.dodo/1/2?hello=world';
        $r = new Request(['uri' => $url]);
        $this->assertEquals($url, $r->getRequestTarget());

        # from Uri instance
        $uri = new Uri($url);
        $r = new Request(['uri' => $uri]);
        $this->assertEquals($url, $r->getRequestTarget());
    }

    function testWithRequestTarget()
    {
        $r = new Request();
        $target = "home://sweet.home/my/room/?temperature=19&degree=c";
        $r = $r->withRequestTarget($target);
        $this->assertEquals($target, $r->getRequestTarget());
    }

    function testGetMethod()
    {
        $r = new Request();
        $this->assertEquals('GET', $r->getMethod());

        $r = new Request(['method' => 'PROXY']);
        $this->assertEquals('PROXY', $r->getMethod());
    }

    function testWithMethod()
    {
        $r = new Request();
        $r = $r->withMethod("BONJOUR");

        $this->assertEquals('BONJOUR', $r->getMethod());
    }

    function testGetUri()
    {
        $r = new Request();
        $uri = $r->getUri();

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertImplements(UriInterface::class, $uri);

        $url = 'ios://apple.com/iphone/MODEL/map';
        $r = new Request(['uri' => $url]);

        $this->assertImplements(UriInterface::class, $r->getUri());
    }

    function testWithUri()
    {
        $r = new Request();

        $url = 'android://google.com/maps/LATITUDE/LANGITUDE';
        $uri = new Uri($url);

        # preserveHost is false (by default)
        $r = $r->withUri($uri, false);
        $this->assertImplements(UriInterface::class, $r->getUri());
        $this->assertEquals($url, $r->getUri());
        $this->assertEquals(['google.com'], $r->getHeader('host'));

        # preserveHost is true
        # when host is not defined, update the host header
        $r = new Request();
        $this->assertEquals([], $r->getHeader('host'));
        $r = $r->withUri($uri, true);
        $this->assertEquals(['google.com'], $r->getHeader('host'));

        # when host header is missing, and Uri does not contains the host
        # do not update
        $r = new Request();
        $uri = new Uri();
        $this->assertEquals([], $r->getHeader('host'));
        $r = $r->withUri($uri, true);
        $this->assertEquals([], $r->getHeader('host'));

        # when host header is empty, and Uri does not contains the host
        # do not update
        $r = new Request();
        $r = $r->withHeader("Host", "");
        $this->assertEquals([""], $r->getHeader('host'));
        $r = $r->withUri($uri, true);
        $this->assertEquals([""], $r->getHeader('host'));

        # when host is already defined, do not update the host header
        $r = new Request(['uri' => "text://matrix.net/wake-up/neo"]);
        $uri = new Uri($url);
        $this->assertEquals(["matrix.net"], $r->getHeader('host'));
        $r = $r->withUri($uri, true);
        $this->assertEquals(['matrix.net'], $r->getHeader('host'));
    }
}
