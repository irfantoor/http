<?php

use IrfanTOOR\Test;
use IrfanTOOR\Engine;
use IrfanTOOR\Http\{
    Message,
    Response
};
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\{
    MessageInterface,
    ResponseInterface,
};

class ResponseTest extends Test
{
    function getResponse(
        $status  = 200,
        $headers = [],
        $body    = ''
    ){
        return new Response([
            'status'  => $status,
            'headers' => $headers,
            'body'    => $body,
        ]);
    }

    function testResponseInstance()
    {
        $response = $this->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(Message::class, $response);
        $this->assertImplements(MessageInterface::class, $response);
        $this->assertImplements(StatusCodeInterface::class, $response);
        $this->assertImplements(ResponseInterface::class, $response);
    }

    function testDefaults()
    {
        $response = $this->getResponse();

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $this->assertEquals('', $response->getBody());
    }

    function testStatusCode()
    {
        $r = new Response();
        $this->assertEquals(200, $r->getStatusCode());

        $r = new Response([
            'status_code' => 666
        ]);
        $this->assertEquals(666, $r->getStatusCode());

        $r = $r->withStatus(999);
        $this->assertEquals(999, $r->getStatusCode());

        $r = $r->withStatus(0);
        $this->assertEquals(0, $r->getStatusCode());

        $r = $r->withStatus(-1);
        $this->assertEquals(-1, $r->getStatusCode());
    }

    function testReasonPhrase()
    {
        $r = new MockResponse();
        $phrases = $r->getPhrases();

        # default phrases
        foreach ($phrases as $code => $phrase)
        {
            $r = new Response([
                'status_code' => $code
            ]);

            $this->assertEquals($phrase, $r->getReasonPhrase());
        }

        # default phrase for unknown status
        $r = new Response(['status_code' => 333]);
        $this->assertEquals("Unknown", $r->getReasonPhrase());

        # predefined status
        $r = new Response(['status_code' => 0, 'reason_phrase' => "Deviation zero"]);
        $this->assertEquals("Deviation zero", $r->getReasonPhrase());
    }
}


class MockResponse extends Response
{
    function getPhrases()
    {
        return self::$phrases;
    }
}
