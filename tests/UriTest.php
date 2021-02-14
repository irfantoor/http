<?php

use IrfanTOOR\Http\Uri;
use IrfanTOOR\Test;
use Psr\Http\Message\UriInterface;

class UriTest extends Test
{
    function testUriInstance()
    {
        $uri = new Uri();

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertImplements(UriInterface::class, $uri);
    }

    function testUriDefaults()
    {
        $uri = new Uri;

        $this->assertEquals('', $uri->getScheme());
        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('', $uri->getHost());
        $this->assertEquals(null, $uri->getPort());
        $this->assertEquals('', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
        $this->assertEquals('', $uri->getAuthority());

        $this->assertEquals('', (string) $uri);
    }

    function testUriInit()
    {
        # with string
        $url = 'https://admin:password@example.com:888/hello/world?action=login&reset=true#top';
        $uri = new Uri($url);

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('admin:password', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals(888, $uri->getPort());
        $this->assertEquals('/hello/world', $uri->getPath());
        $this->assertEquals('action=login&reset=true', $uri->getQuery());
        $this->assertEquals('top', $uri->getFragment());
        $this->assertEquals('admin:password@example.com:888', $uri->getAuthority());
        $this->assertEquals($url, (string) $uri);

        # with array
        $uri = new Uri([
            'scheme' => 'https',
            'user' => 'admin',
            'pass' => 'password',
            'host' => 'example.com',
            'port' => 888,
            'path' => '/hello/world',
            'query' => 'action=login&reset=true',
            'fragment' => 'top'
        ]);

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('admin:password', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals(888, $uri->getPort());
        $this->assertEquals('/hello/world', $uri->getPath());
        $this->assertEquals('action=login&reset=true', $uri->getQuery());
        $this->assertEquals('top', $uri->getFragment());
        $this->assertEquals('admin:password@example.com:888', $uri->getAuthority());
        $this->assertEquals($url, (string) $uri);
    }

    /**
     * throws: InvalidArgumentException::class
     */
    function testExceptionWhenUriIsInvalid()
    {
        new Uri(':');
    }

    function testGetScheme()
    {
        $uri = new Uri('example.com');
        $this->assertEquals('', $uri->getScheme());

        $uri = new Uri('http://example.com');
        $this->assertEquals('http', $uri->getScheme());

        $uri = new Uri('ftp://example.com');
        $this->assertEquals('ftp', $uri->getScheme());

        $uri = new Uri('example.com:443');
        $this->assertEquals('', $uri->getScheme());

        $uri = new Uri('http://example.com:443');
        $this->assertEquals('http', $uri->getScheme());
    }

    function users()
    {
        return [
            '',
            '@',
            'user@',
            'user:pass@',
        ];
    }

    function ports()
    {
        return [
            '',
            ':80',
            ':443',
            ':'
        ];
    }

    function testGetAuthority()
    {
        $users  = $this->users();
        $ports = $this->ports();

        foreach ($users as $user) {
            foreach ($ports as $port) {
                $url = 'http://' . $user . 'example.com' . $port . '/hello/world?a=b&c=d#top';
                $uri = new Uri($url);

                $expected = rtrim(ltrim($user . 'example.com' . $port, '@'), ':');
                $this->assertEquals($expected, $uri->getAuthority());
            }
        }
    }

    /**
     * u: $this->users()
     */
    function testGetUserInfo($u)
    {
        $url = 'http://' . $u . 'example.com:80/hello/world?a=b&c=d#top';
        $uri = new Uri($url);

        $this->assertEquals(rtrim($u, '@'), $uri->getUserInfo());
    }

    function testGetHost()
    {
        $users  = $this->users();
        $ports = $this->ports();

        foreach ($users as $user) {
            foreach ($ports as $port) {
                $url = 'http://' . $user . 'ExAmPlE.CoM' . $port . '/hello/world?a=b&c=d#top';
                $uri = new Uri($url);

                # does not normalizes to lower case
                $this->assertEquals('ExAmPlE.CoM', $uri->getHost());
            }
        }

        $uri = new Uri();
        $this->assertEquals('', $uri->getHost());
    }

    /**
     * p: $this->ports()
     */
    function testGetPort($p)
    {
        $url = 'http://example.com' . $p . '/hello/world?a=b&c=d#top';
        $uri = new Uri($url);

        $this->assertEquals(ltrim($p, ':'), $uri->getPort());
    }

    function testPath()
    {
        $uri = new Uri();
        $this->assertEquals('', $uri->getPath());

        $uri = new Uri('example.com:80/');
        $this->assertEquals('/', $uri->getPath());

        $uri = new Uri('http://example.com/hello/world');
        $this->assertEquals('/hello/world', $uri->getPath());

        $uri = new Uri('http://example.com/hello/world/');
        $this->assertEquals('/hello/world/', $uri->getPath());
    }

    function testQuery()
    {
        $uri = new Uri('example.com?');
        $this->assertEquals('', $uri->getQuery());
        $uri = new Uri('example.com?hello_world');
        $this->assertEquals('hello_world', $uri->getQuery());
        $uri = new Uri('example.com?hello=world&go=google');
        $this->assertEquals('hello=world&go=google', $uri->getQuery());
    }

    function testFragment()
    {
        $uri = new Uri('example.com?#');
        $this->assertEquals('', $uri->getFragment());
        $uri = new Uri('example.com?test=again&#hello-world');
        $this->assertEquals('hello-world', $uri->getFragment());
    }

    function testWithScheme()
    {
        $uri = new Uri();
        $uri = $uri->withScheme('HTTP');
        $this->assertEquals('HTTP', $uri->getScheme());

        $uri = $uri->withScheme('');
        $this->assertEquals('', $uri->getScheme());
    }

    function testwithUserInfo()
    {
        $uri = new Uri();
        $uri = $uri->withUserInfo('user');
        $this->assertEquals('user', $uri->getUserInfo());

        $uri = $uri->withUserInfo('user', 'pass');
        $this->assertEquals('user:pass', $uri->getUserInfo());
    }

    function testWithHost()
    {
        $uri = new Uri();
        $uri = $uri->withHost('example.com');
        $this->assertEquals('example.com', $uri->getHost());

        $uri = $uri->withHost('');
        $this->assertEquals('', $uri->getHost());
    }

    function testWithPort()
    {
        $uri = new Uri();
        $uri = $uri->withPort(655364);
        $this->assertEquals(655364, $uri->getPort());

        $uri = $uri->withPort(80);
        $this->assertEquals(80, $uri->getPort());

        $uri = $uri->withPort(null);
        $this->assertEquals(null, $uri->getPort());
    }

    function testWithPath()
    {
        $uri = new Uri();
        $uri = $uri->withPath("/absolute/path/");
        $this->assertEquals("/absolute/path/", $uri->getPath());

        $uri = $uri->withPath("relative/path/");
        $this->assertEquals("relative/path/", $uri->getPath());

        $uri = $uri->withPath("/");
        $this->assertEquals("/", $uri->getPath());

        $uri = $uri->withPath("");
        $this->assertEquals("", $uri->getPath());
    }

    function testWithQuery()
    {
        $uri = new Uri();
        $this->assertEquals("", $uri->getQuery());

        $uri = $uri->withQuery("hello=world");
        $this->assertEquals("hello=world", $uri->getQuery());

        $uri = $uri->withQuery("");
        $this->assertEquals("", $uri->getQuery());
    }

    function testWithFragment()
    {
        $uri = new Uri();
        $this->assertEquals("", $uri->getFragment());

        $uri = $uri->withFragment("page-top");
        $this->assertEquals("page-top", $uri->getFragment());

        $uri = $uri->withFragment("");
        $this->assertEquals("", $uri->getFragment());
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    // public function __toString();
    function testToString()
    {
        $uri = new Uri();
        $uri = $uri->withScheme('scheme');
        $this->assertEquals("scheme:", $uri->__toString());

        $uri = $uri->withHost('localhost');
        $this->assertEquals("scheme://localhost/", $uri->__toString());

        $uri = $uri->withPath("");
        $this->assertEquals("scheme://localhost/", $uri->__toString());

        $uri = $uri->withPath("/");
        $this->assertEquals("scheme://localhost/", $uri->__toString());

        $uri = $uri->withQuery("");
        $this->assertEquals("scheme://localhost/", $uri->__toString());

        $uri = $uri->withQuery("hello=world");
        $this->assertEquals("scheme://localhost/?hello=world", $uri->__toString());

        $uri = $uri->withFragment("");
        $this->assertEquals("scheme://localhost/?hello=world", $uri->__toString());

        $uri = $uri->withFragment("top");
        $this->assertEquals("scheme://localhost/?hello=world#top", $uri->__toString());
    }
}
