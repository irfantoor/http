<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\Message;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

/**
 * Response -- A message sent from a server to the client
 */
class Response extends Message implements StatusCodeInterface, ResponseInterface
{
    protected static $phrases;

    /** @var int */
    protected $status_code;

    /** @var string */
    protected $reason_phrase;

    public function __construct(array $init = [])
    {
        parent::__construct($init);

        # convert all the constants from StatusCodeInterface to reason phrases
        if (!self::$phrases) {
            $rc = new ReflectionClass(__CLASS__);
            $constants = $rc->getConstants();

            foreach ($constants as $k => $v)
                self::$phrases[$v] = str_replace('STATUS_', '', $k);
        }

        # version
        $this->status_code = $init['status_code'] ?? 200;

        # uri
        $this->reason_phrase = $init['reason_phrase'] ?? "";
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->clone(
            [
                'status_code'   => $code,
                'reason_phrase' => $reasonPhrase
            ]
        );
    }

    public function getReasonPhrase()
    {
        return
            $this->reason_phrase === ""
            ? (self::$phrases[$this->status_code] ?? "Unknown")
            : $this->reason_phrase
        ;
    }

    /**
     * Send this response to the client
     */
    public function send()
    {
        # send status header
        $status = $this->getStatusCode();
        $http_line = sprintf('HTTP/%s %s %s',
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase()
        );

        header($http_line);

        # send other headers
        foreach ($this->getHeaders() as $k => $v)
        {
            header($k . ":" . $this->getHeaderLine($k));
        }

        # send body
        $stream = $this->getBody();

        if ($stream->isSeekable())
            $stream->rewind();

        while (!$stream->eof()) {
            echo $stream->read(8192);
        }

        $stream->close();
    }
}
