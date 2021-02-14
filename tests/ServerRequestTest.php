<?php

use IrfanTOOR\Http\{
    Cookie,
    Message,
    Request,
    ServerRequest,
    UploadedFile,
    Uri
};

use Fig\Http\Message\RequestMethodInterface;

use Psr\Http\Message\{
    MessageInterface,
    RequestInterface,
    StreamInterface,
    UploadedFileInterface,
    UriInterface
};


use IrfanTOOR\Test;

class ServerRequestTest extends Test
{
    function getServerRequest($env=[])
    {
        return new ServerRequest($env);
    }

    function testServerRequestInstance()
    {
        $request = $this->getServerRequest();

        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Message::class, $request);
        $this->assertImplements(RequestInterface::class, $request);
        $this->assertImplements(MessageInterface::class, $request);
    }

    function testDefaults()
    {
        $request = $this->getServerRequest();

        $this->assertEquals('1.1', $request->getProtocolVersion());
        $this->assertArray($request->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('', (string) $request->getBody());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://localhost/', (string) $request->getUri());

        $this->assertEquals($_COOKIE, $request->getCookieParams());
        $this->assertEquals($_GET, $request->getQueryParams());
        $this->assertEquals($_FILES, $request->getUploadedFiles());
        $this->assertEquals($_POST, $request->getParsedBody());
        $this->assertEquals([], $request->getAttributes());
    }

    function testRequestWithInit()
    {
        $url = 'https://example.com:8080/hello/world?something=here&somewhat=present';
        $r = $this->getServerRequest([
            'method' => 'POST',
            'uri' => $url,
        ]);

        $this->assertEquals('POST', $r->getMethod());
        $uri = $r->getUri();

        $this->assertEquals($url, (string) $uri);
        $this->assertEquals($url, $uri->__toString());
    }

    function testgetServerParams()
    {
        $r = new ServerRequest();
        $params = array_merge($_SERVER, getenv());
        $server_params = $r->getServerParams();

        foreach ($params as $k => $v) {
            $this->assertEquals($v, $server_params[$k]);
        }

        $r  = new ServerRequest([
            'server' => [
                'REQUEST_TIME' => 0,
                'Hello'        => 'World!',
            ]
        ]);

        $server_params = $r->getServerParams();
        $this->assertEquals(0, $server_params['REQUEST_TIME']);
        $this->assertEquals('World!', $server_params['Hello']);
    }

    function testGetCookieParams()
    {
        $r = new ServerRequest([
            'cookies' => ['hello' => 'world']
        ]);

        $cookies = $r->getCookieParams();

        $this->assertEquals(1, count($cookies));

        $cookie = $cookies[0];
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals('hello', $cookie['name']);
        $this->assertEquals('world', $cookie['value']);
    }

    function testWithCookieParams()
    {
        $r = new ServerRequest();
        $cookies = $r->getCookieParams();
        $this->assertEquals(0, count($cookies));


        $r = $r->withCookieParams(['hello' => 'world']);
        $cookies = $r->getCookieParams();

        $this->assertEquals(1, count($cookies));
        $cookie = $cookies[0];
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals('hello', $cookie['name']);
        $this->assertEquals('world', $cookie['value']);
    }

    function testGetQueryParams()
    {
        $r = new ServerRequest([
            'get' => [
                'hello' => 'world'
            ]
        ]);

        $this->assertEquals(['hello' => 'world'], $r->getQueryParams());
    }

    function testWithQueryParams()
    {
        $r = new ServerRequest();
        $r2 = $r->WithQueryParams(['hello' => 'world']);

        $this->assertEquals([], $r->getQueryParams());
        $this->assertEquals(['hello' => 'world'], $r2->getQueryParams());
    }

    function testGetUploadedFiles()
    {
        $r = new ServerRequest();
        $this->assertEquals([], $r->getUploadedFiles());
    }

    function testWithUploadedFiles()
    {
        $r = new ServerRequest();
        $this->assertEquals([], $r->getUploadedFiles());

        $r = $r->withUploadedFiles(
            [
                [
                    'file' => __FILE__,
                    'name' => 'ServerRequestTest.php',
                ]
            ]
        );

        $files = $r->getUploadedFiles();
        $this->assertArray($files);

        $file = $files[0];
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertImplements(UploadedFileInterface::class, $file);
    }

    function testGetParsedBody()
    {
        $r = new ServerRequest(
            [
                'post' => [
                    'hello' => 'world'
                ]
            ]
        );

        $this->assertEquals(['hello' => 'world'], $r->getParsedBody());
    }

    function testWithParsedBody()
    {
        $r = new ServerRequest();
        $r = $r->withParsedBody(
            [
                'hello' => 'world'
            ]
        );

        $this->assertEquals(['hello' => 'world'], $r->getParsedBody());
    }

    function testGetAttributes()
    {
        $attributes = [
            'greeting' => 'hello',
            'greeted'  => 'world'
        ];

        $r = new ServerRequest(['attributes' => $attributes]);

        $this->assertEquals($attributes, $r->getAttributes());
    }

    function testGetAttribute()
    {
        $attributes = [
            'greeting' => 'hello',
            'greeted'  => 'world'
        ];

        $r = new ServerRequest(['attributes' => $attributes]);

        $this->assertEquals('hello', $r->getAttribute('greeting'));
        $this->assertEquals('world', $r->getAttribute('greeted'));
        $this->assertEquals('french', $r->getAttribute('language', 'french'));
    }

    function testWithAttribute()
    {
        $attributes = [
            'greeting' => 'hello',
            'greeted'  => 'world'
        ];

        $r = new ServerRequest(['attributes' => $attributes]);
        $r = $r->withAttribute('language', 'c');
        $this->assertEquals('c', $r->getAttribute('language', 'french'));
    }

    function testWithoutAttribute()
    {
        $attributes = [
            'greeting' => 'hello',
            'greeted'  => 'world'
        ];

        $r = new ServerRequest(['attributes' => $attributes]);
        $r = $r->withoutAttribute('greeted');
        $this->assertNull($r->getAttribute('greeted'));
    }
}
