<?php

use IrfanTOOR\Http\Environment;
use IrfanTOOR\Test;

class EnvironmentTest extends Test
{
    public function testInstanceOfEnvironment()
    {
        $env = new Environment();
        $this->assertInstanceOf(Environment::class, $env);
    }

    public function testDefaultValues()
    {
        $env = new Environment();

        $data = array_merge($_SERVER, getenv());
        foreach ($data as $k => $v) {
            $this->assertEquals($v, $env[$k]);
        }
        
    }

    public function testEnvironmentAssignedValues()
    {
        $env = new Environment(
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

        $this->assertEquals('hello', $env['name']);
        $this->assertEquals('world', $env['value']);
        $this->assertEquals(10, $env['expires']);
        $this->assertEquals('/blog', $env['path']);
        $this->assertEquals('irfantoor.com', $env['domain']);
        $this->assertTrue($env['secure']);
        $this->assertTrue($env['httponly']);
    }
}
