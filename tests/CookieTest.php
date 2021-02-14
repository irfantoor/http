<?php

use IrfanTOOR\Http\Cookie;
use IrfanTOOR\Test;

class CookieTest extends Test
{
    public function testInstanceOfCookie()
    {
        $cookie = new Cookie();
        $this->assertInstanceOf(Cookie::class, $cookie);
    }

    public function testDefaultValues()
    {
        $cookie = new Cookie();

        $this->assertEquals('undefined', $cookie['name']);
        $this->assertNull($cookie['value']);
        $this->assertEquals(1 , $cookie['expires']);
        $this->assertEquals('/', $cookie['path']);
        $this->assertEquals('localhost', $cookie['domain']);
        $this->assertFalse($cookie['secure']);
        $this->assertFalse($cookie['httponly']);
    }

    public function testCookieAssignedValues()
    {
        $cookie = new Cookie(
            [
                'name'     => 'hello',
                'value'    => 'world',
                'expires'  => 10,
                'path'     => '/blog',
                'domain'   => 'irfantoor.com',
                'secure'   => true,
                'httponly' => true,
            ]
        );

        $this->assertEquals('hello', $cookie['name']);
        $this->assertEquals('world', $cookie['value']);
        $this->assertEquals(10, $cookie['expires']);
        $this->assertEquals('/blog', $cookie['path']);
        $this->assertEquals('irfantoor.com', $cookie['domain']);
        $this->assertTrue($cookie['secure']);
        $this->assertTrue($cookie['httponly']);
    }

    public function testCookieExpires()
    {
        $cookie = new Cookie();
        $this->assertEquals(1, $cookie['expires']);

        $cookie = new Cookie(['value' => 'test']);
        $this->assertEquals(time() + 24 * 60 * 60, $cookie['expires']);
    }
}
