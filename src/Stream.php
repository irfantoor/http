<?php

namespace IrfanTOOR\Http;

use Exception;
use RunTimeException;
use Psr\Http\Message\StreamInterface;
use Throwable;

/**
 * A data stream.
 */
class Stream implements StreamInterface
{
    protected static $modes = [
        # mode => [read,  write, seek,  trunc.]
        'r'    => [true,  false, true,  false ], # bof
        'r+'   => [true,  true,  true,  false ], # bof
        'w'    => [false, true,  false, true  ], # bof
        'w+'   => [true,  true,  true,  true  ], # bof
        'a'    => [false, true,  false, false ], # eof always appends
        'a+'   => [true,  true,  true,  false ], # eof fseek in readmode, write appends
    ];

    /** @var Resource */
    protected $resource = null;

    /** @var array -- meta data [$key => $value ...] */
    protected $metadata = [];

    /** @var bool -- wether the stream is readeable */
    protected $is_readable = false;

    /** @var bool -- wether the stream is writable */
    protected $is_writable = false;

    /** @var bool -- wether the stream is writable */
    protected $is_seekable = false;

    /**
     * Stream constructor
     *
     * @param mixed $mixed    Following types can be used:
     *                          null     - create a ctream with no contents
     *                          resource - create a stream from this resource
     *                          string   - create a stream with this content
     *                          object   - use __toString to convert to a string ...
     *                          array    - create a stream from filename, mode
     * @param array $metadata Associative array of meta data
     * @throws \InvalidArgumentException If the mixed argument is not vlaid.
     */
    public function __construct($mixed = null, array $metadata = [])
    {
        $this->metadata = $metadata;

        if ($mixed === null) {
            $this->fopen('php://temp', 'w');
        } elseif (is_resource($mixed)) {
            $this->resource = $mixed;
            $meta_data = $this->getMetaData();
            $mode = str_replace('b', '', $meta_data['mode']);
            $this->adjustMode($mode);

        } elseif (
            is_string($mixed)
            || (is_object($mixed) && (method_exists($mixed, '__toString')))
        ) {
            $this->fopen('php://temp', 'w+');
            $this->write((string) $mixed);
        } elseif (is_array($mixed) && isset($mixed['file'])) {
            $this->fopen($mixed['file'], $mixed['mode'] ?? 'r');
        } else {
            throw new \InvalidArgumentException('The first argument is not valid.');
        }
    }

    protected function adjustMode($mode)
    {
        if (!array_key_exists($mode, self::$modes))
            $mode = 'r';

        $def = self::$modes[$mode];

        $this->is_readable = $def[0];
        $this->is_writable = $def[1];
        $this->is_seekable = $def[2];

        return $mode;
    }

    protected function fopen($file, $mode = 'r')
    {
        $mode = $this->adjustMode($mode);
        $this->resource = fopen($file, $mode);
    }

    function __destruct()
    {
        $this->close();
    }

    function __clone()
    {
        $pos = $this->tell();
        $this->rewind();
        $contents = $this->getContents();
        $this->seek($pos);

        $this->resource = fopen('php://temp', 'w+');
        $this->write($contents);
        $this->seek($pos);
    }

    public function __toString()
    {
        try {
            if ($this->isSeekable())
                $this->rewind();

            return $this->getContents();
        } catch (\Throwable $e) {
            return "";
        }
    }

    public function close()
    {
        if (is_resource($this->resource))
            fclose($this->resource);

        $this->resource = null;
        $this->metadata = [];
    }

    public function detach()
    {
        if ($this->resource) {
            $resource = $this->resource;
            $this->resource = null;
            return $resource;
        }

        return null;
    }

    public function attach(&$resource)
    {
        $this->resource = $resource;
    }

    public function getSize()
    {
        return
            is_resource($this->resource)
            ? fstat($this->resource)['size'] ?? null
            : null
        ;
    }

    public function tell()
    {
        try {
            if ($this->resource)
                return ftell($this->resource);
            else
                throw new Exception("Unable to tell.");
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function eof()
    {
        return $this->resource ? feof($this->resource) : true;
    }

    public function isSeekable()
    {
        return $this->resource ? $this->is_seekable : false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        try {
            return
                $this->isSeekable()
                ? fseek($this->resource, $offset, $whence)
                : false;
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function rewind()
    {
        if ($this->isSeekable())
            rewind($this->resource);
        else
            throw new RuntimeException("The stream is not seekable.");
    }

    public function isWritable()
    {
        return ($this->resource && $this->is_writable) ? true : false;
    }

    public function write($string)
    {
        try {
            if (!$this->isWritable())
                return false;

            return fwrite($this->resource, $string);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function isReadable()
    {
        return ($this->resource && $this->is_readable) ? true : false;
    }

    public function read($length)
    {
        if (!$this->isReadable())
            return false;

        try {
            return fread($this->resource, $length);
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getContents()
    {
        try {
            $remaining = "";

            while (!$this->eof()) {
                $remaining .= fread($this->resource, 8192);
            }

            return $remaining;
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function getMetadata($key = null)
    {
        if (!is_resource($this->resource))
            return null;

        $data = stream_get_meta_data($this->resource);
        return $key ? ($data[$key] ?? null) : $data;
    }
}
