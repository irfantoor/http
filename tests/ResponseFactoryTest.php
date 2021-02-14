<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\Response;
use IrfanTOOR\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ResponseFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new ResponseFactory();

        $this->assertInstanceOf(ResponseFactory::class, $factory);
        $this->assertImplements(ResponseFactoryInterface::class, $factory);
    }

    function testCreateResponse()
    {
        $factory = new ResponseFactory();

        $response = $factory->createResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertImplements(ResponseInterface::class, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());

        $response = $factory->createResponse(404, "NOT_PRESENT");
        $this->assertInstanceOf(Response::class, $response);
        $this->assertImplements(ResponseInterface::class, $response);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('NOT_PRESENT', $response->getReasonPhrase());
    }
}
