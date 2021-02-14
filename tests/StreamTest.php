<?php

use IrfanTOOR\Http\Stream;
use IrfanTOOR\Test;
use Psr\Http\Message\StreamInterface;

class StreamTest extends Test
{
    public function testInstance()
    {
        $s = new Stream();
        $this->assertInstanceOf(Stream::class, $s);
        $this->assertImplements(StreamInterface::class, $s);
    }

    function testInitResource()
    {
        $fp = fopen(__FILE__, 'r');
        fseek($fp, 10);
        $stream = new Stream($fp);

        # keeps the position of resource
        $fp = fopen('test.txt', 'w+');
        fwrite($fp, 'hello world!');
        fclose($fp);
        $fp = fopen('test.txt', 'r+');
        $stream = new Stream($fp);

        $this->assertEquals(0, $stream->tell());
        $this->assertEquals('hello world!', (string) $stream);
        $this->assertEquals(12, $stream->getSize());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertArray($stream->getMetadata());
        $this->assertNotZero(count($stream->getMetadata()));
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    function testInitArray()
    {
        $stream = new Stream(
            [
                'file' => 'test.txt',
                'mode' => 'w+'
            ]
        );

        $stream->write('data');

        $this->assertEquals('data', (string) $stream);
        $this->assertEquals(4, $stream->getSize());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertArray($stream->getMetadata());
        $this->assertNotZero(count($stream->getMetadata()));
        $this->assertTrue($stream->eof());
        $stream->close();

        $stream = new Stream(
            [
                'file' => 'test.txt',
                'mode' => 'r',
            ]
        );

        $contents = file_get_contents('test.txt');
        $this->assertEquals($contents, (string) $stream);
        $this->assertEquals(strlen($contents), $stream->getSize());
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertArray($stream->getMetadata());
        $this->assertNotZero(count($stream->getMetadata()));
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    function testInitString()
    {
        $stream = new Stream('hello world!');
        $this->assertEquals('hello world!', (string) $stream);
        $this->assertEquals(12, $stream->getSize());
    }

   /**
     * throws: InvalidArgumentException::class
     * message: The first argument is not valid.
     */
    public function testInitException()
    {
        new Stream(true);
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        $stream->close();
        $this->assertFalse(is_resource($handle));

        $handle = fopen('php://temp', 'r');
        $stream = new Stream($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    function testToString()
    {
        $stream = new Stream();
        $this->assertEquals('', $stream->__toString());

        $stream = new Stream('hello world!');
        $this->assertEquals('hello world!', $stream->__toString());

        $contents = file_get_contents(__FILE__);
        $fp = fopen(__FILE__, 'r');
        $stream = new Stream($fp);
        $this->assertEquals($contents, (string) $stream);

        $stream = new Stream(['file' => 'test.txt', 'mode' => 'w+']);
        $stream->write('hello');
        $stream->write(' ');
        $stream->write('world!');
        $this->assertEquals('hello world!', $stream->__toString());

        $stream = new Stream(['file' => 'test.txt', 'mode' => 'r']);
        $this->assertEquals('hello world!', $stream->__toString());

        # does not throw the exception encountered
        $s = new MockStream();
        $this->assertEquals("", $s->__toString());

        unlink('test.txt');
    }

    function testClose()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream($handle);
        $stream->close();

        $this->assertEmpty($stream->getMetadata());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
    }

    function testDetach()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream($handle);
        $resource = $stream->detach();

        $this->assertEmpty($stream->getMetadata());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());

        $this->assertEquals($resource, $handle);
        fclose($resource);
    }

    function testGetSize()
    {
        $stream = new Stream();
        $this->assertEquals(0, $stream->getSize());

        $stream = new Stream('hello world!');
        $this->assertEquals(12, $stream->getSize());

        $stream->write('!!');
        $this->assertEquals(14, $stream->getSize());

        $stream->close();
        $this->assertNull($stream->getSize());
    }

    function testTell()
    {
        $stream = new Stream();
        $this->assertEquals(0, $stream->tell());

        $stream = new Stream('hello world!');
        $this->assertEquals(12, $stream->tell());

        $stream->seek(-2, SEEK_CUR);
        $this->assertEquals(10, $stream->tell());
    }

    /**
     * throws: RuntimeException::class
     * message: Unable to tell.
     */
    function testTellException()
    {
        $stream = new Stream();
        $stream->close();
        $stream->tell();
    }

    function testEof()
    {
        $stream = new Stream('');
        $this->assertFalse($stream->eof());

        $c = $stream->read(1);
        $this->assertEquals('', $c);
        $this->assertTrue($stream->eof());

        $stream = new Stream('hello world!');
        $this->assertFalse($stream->eof());

        $stream->read(1);
        $this->assertTrue($stream->eof());

        $stream->close();
        $this->assertTrue($stream->eof());
    }

    function testIsSeekable()
    {
        $stream = new Stream('');
        $this->assertTrue($stream->isSeekable());

        $fp = fopen('test.txt', 'w+');
        fwrite($fp, 'hello world!');
        fclose($fp);

        foreach (MockStream::getModes() as $mode => $def) {
            $stream = new Stream(['file' => 'test.txt' , 'mode' => $mode]);
            if ($def[2] !== $stream->isSeekable()) {
                $stream->seek(6);
                dd($stream->getContents());
            }
            $this->assertEquals($def[2], $stream->isSeekable());
        }
    }

    function testSeek()
    {
        $fp = fopen('test.txt', 'w+');
        fwrite($fp, 'hello world!');
        fclose($fp);

        $stream = new Stream(['file' => 'test.txt' , 'mode' => 'r']);
        $this->assertEquals(0, $stream->tell());

        # SEEK_SET (default)
        $stream->seek(6);
        $this->assertEquals('world!', $stream->getContents());
        $stream->seek(6, SEEK_SET);
        $this->assertEquals('world!', $stream->getContents());

        # SEEK_CUR (capabale of the pinser movement!)
        $stream->seek(6); # at w or world
        $stream->seek(4, SEEK_CUR); # relative movement ahead
        $this->assertEquals('d!', $stream->getContents());

        $stream->seek(6); # at w or world
        $stream->seek(-4, SEEK_CUR); # relative movement backwards
        $this->assertEquals('llo world!', $stream->getContents());

        # SEEK_END (capable of virtual pincer)
        $stream->seek(6); # at w or world
        $stream->seek(-4, SEEK_END); # its from the end
        $this->assertEquals('rld!', $stream->getContents());
        $this->assertZero($stream->seek(4, SEEK_END)); # its beyond END!
        $this->assertEquals(16, $stream->tell());
        file_put_contents('test.txt', 'hello world! go man go');
        # it will return the contents from the seeked position.
        $this->assertEquals('man go', $stream->getContents());

        unlink('test.txt');
    }

    function testRewind()
    {
        $contents = 'hello world!';
        $fp = fopen('test.txt', 'w+');
        fwrite($fp, $contents);
        fclose($fp);

        foreach (MockStream::getModes() as $mode => $def) {
            if (!$def[2])
                continue;

            $stream = new Stream(['file' => 'test.txt' , 'mode' => $mode]);
            $this->assertTrue($stream->isSeekable());
            $stream->seek(6);
            $this->assertEquals(6, $stream->tell());
            $stream->rewind();
            $this->assertEquals(0, $stream->tell());
        }
    }

    /**
     * throws: RuntimeException::class
     * message: The stream is not seekable.
     */
    function testRewindException()
    {
        $contents = 'hello world!';
        $fp = fopen('test.txt', 'w+');
        fwrite($fp, $contents);
        fclose($fp);

        foreach (MockStream::getModes() as $mode => $def) {
            if ($def[2])
                continue;

            $stream = new Stream(['file' => 'test.txt' , 'mode' => $mode]);
            $this->assertFalse($stream->isSeekable());
            $stream->rewind();
        }
    }

    function testIsWritable()
    {
        $stream = new Stream('');
        $this->assertTrue($stream->isSeekable());

        $fp = fopen('test.txt', 'w+');
        fwrite($fp, 'hello world!');
        fclose($fp);

        foreach (MockStream::getModes() as $mode => $def) {
            $stream = new Stream(['file' => 'test.txt' , 'mode' => $mode]);
            $this->assertEquals($def[1], $stream->isWritable());
        }
    }

    function testWrite()
    {
        $stream = new Stream('');
        $this->assertEquals('', (string) $stream);

        $count = $stream->write('hello');
        $this->assertEquals(5, $count);
        $this->assertEquals('hello', (string) $stream);

        $count = $stream->write(' ');
        $this->assertEquals(1, $count);
        $this->assertEquals('hello ', (string) $stream);

        $count = $stream->write('world!');
        $this->assertEquals(6, $count);
        $this->assertEquals('hello world!', (string) $stream);
    }

    function testIsReadable()
    {
        $stream = new Stream('');
        $this->assertTrue($stream->isSeekable());

        $fp = fopen('test.txt', 'w+');
        fwrite($fp, 'hello world!');
        fclose($fp);

        foreach (MockStream::getModes() as $mode => $def) {
            $stream = new Stream(['file' => 'test.txt' , 'mode' => $mode]);
            $this->assertEquals($def[0], $stream->isReadable());
        }
    }

    function testRead()
    {
        $stream = new Stream('hello world!');

        $stream->rewind();
        $string = $stream->read(5);
        $this->assertEquals('hello', $string);

        $string = $stream->read(1);
        $this->assertEquals(' ', $string);

        $string = $stream->read(6);
        $this->assertEquals('world!', $string);

        $string = $stream->read(6);
        $this->assertEquals('', $string);
    }

    function testGetContents()
    {
        $stream = new Stream('hello world!');
        $stream->rewind();
        $this->assertEquals('hello world!', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
        $stream->seek(6);
        $this->assertEquals('world!', $stream->getContents());
        $this->assertTrue($stream->eof());
    }

    function testGetMetadata()
    {
        $contents = 'hello world!';
        $fp = fopen('test.txt', 'w+');
        fwrite($fp, $contents);
        fclose($fp);

        $stream = new Stream(['file' => 'test.txt', 'mode' => 'a+']);
        $meta_data = $stream->getMetaData();
        $resource = $stream->detach();
        $stream_meta_data = stream_get_meta_data($resource);

        $this->assertEquals($meta_data, $stream_meta_data);
        $this->assertEquals('a+', $meta_data['mode']);
        $this->assertEquals('test.txt', $meta_data['uri']);
    }

    public function testCloneStream()
    {
        $s = new Stream('');
        $s->write('something');
        $t = clone $s;

        $this->assertNotEquals($s, $t);
        $this->assertNotSame($s, $t);
        $this->assertEquals((string)$s, (string)$t);

        $t->write('else');
        $this->assertNotEquals((string)$s, (string)$t);
    }
}

class MockStream extends Stream
{
    public static function getModes()
    {
        return self::$modes;
    }

    public function getContents()
    {
        throw new Exception("__toString must not propagate this exception");
    }
}
