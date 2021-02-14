<?php

use IrfanTOOR\Test;

use IrfanTOOR\Http\{
    Stream,
    UploadedFile
};

use Psr\Http\Message\{
    StreamInterface,
    UploadedFileInterface
};

class UploadedFileTest extends Test
{
    function createTemporaryFile($contents = null)
    {
        $file = tempnam(sys_get_temp_dir(), uniqid());

        if ($contents) {
            file_put_contents($file, $contents);
        }

        return $file;
    }

    function assertUploadedFile($file)
    {
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertImplements(UploadedFileInterface::class, $file);
    }

    function assertUploadedContents($file, $contents)
    {
        $this->assertSame($contents, (string) $file->getStream());
        $this->assertSame(strlen($contents), $file->getSize());
        $this->assertSame($error ?: UPLOAD_ERR_OK, $file->getError());
    }

    function assertUploadedClientFilenameAndType($file, $clientFilename = null, $clientMediaType = null) {
        $this->assertSame($clientFilename, $file->getClientFilename());
        $this->assertSame($clientMediaType, $file->getClientMediaType());
    }

    function testInstance()
    {
        $file = new UploadedFile();
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertImplements(UploadedFileInterface::class, $file);
    }

    /**
     * throws: RuntimeException::class
     * message: Stream can not be created.
     */
    function testDefaults()
    {
        $file = new UploadedFile();
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $file->getError());
        $file->getStream();
    }

    function testInit()
    {
        $file = new UploadedFile(['file' => __FILE__]);
        $this->assertInstanceOf(Stream::class, $file->getStream());
        $this->assertImplements(StreamInterface::class, $file->getStream());

        $contents = file_get_contents(__FILE__);
        $this->assertEquals($contents, (string) $file->getStream());
        # it returns the size provided and not the actual size of the content
        $this->assertEquals(0, $file->getSize());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
        $this->assertEquals('', $file->getClientFilename());
        $this->assertEquals('', $file->getClientMediaType());
    }

    public function testCreateUploadedFileWithString()
    {
        $file = new UploadedFile(['file' => __FILE__]);
        $contents = file_get_contents(__FILE__);
        $size = strlen($contents);

        $this->assertUploadedFile($file, $contents, $size);
    }

    public function testCreateUploadedFileWithClientFilenameAndMediaType()
    {
        $contents = 'this is your capitan speaking';
        $upload = $this->createTemporaryFile($contents);
        $error = UPLOAD_ERR_OK;
        $clientFilename = 'test.txt';
        $clientMediaType = 'text/plain';
        $size = strlen($contents);

        $file = new UploadedFile(
            [
                'file' => $upload,
                'name' => $clientFilename,
                'type' => $clientMediaType,
                'size' => $size,
                'error' => $error
            ]
        );

        $this->assertUploadedFile($file, $contents, null, $error, $clientFilename, $clientMediaType);
    }

    public function testCreateUploadedFileWithError()
    {
        $error = UPLOAD_ERR_NO_FILE;
        $file = new UploadedFile(['file' => null]);

        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertImplements(UploadedFileInterface::class, $file);
        $this->assertEquals($error, $file->getError());
    }

    /**
     * throws: RuntimeException::class
     * message: No stream is available.
     */
    function testGetStream()
    {
        $tmpfile = $this->createTemporaryFile('hello world!');
        $file = new UploadedFile(['file' => $tmpfile]);

        $stream = $file->getStream();
        $this->assertEquals('hello world!', (string) $stream);

        $file->moveTo('test.txt');
        unlink('test.txt');

        $file->getStream();
    }

    /**
     * throws: InvalidArgumentException::class
     * message: Target path is not a string.
     */
    function testMoveToTargetPathException()
    {
        $file = new UploadedFile(['file' => __FILE__]);
        $file->moveTo(123);
    }

    /**
     * throws: RuntimeException::class
     * message: File has already been moved
     */
    function testMoveToFileMovedException()
    {
        $tmpfile = $this->createTemporaryFile('hello world!');
        $file = new UploadedFile(['file' => $tmpfile]);
        $file->moveTo('test.txt');
        unlink('test.txt');

        $file->moveTo('this_must_fail.txt');
    }

    function testMoveTo()
    {
        $tmpfile = $this->createTemporaryFile('hello world!');
        $file = new UploadedFile(['file' => $tmpfile]);
        $this->assertFile($tmpfile);

        $file->moveTo('test.txt');
        $this->assertEquals('hello world!', file_get_contents('test.txt'));
        $this->assertNotFile($tmpfile);

        unlink('test.txt');
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    // public function getSize();
    function testGetSize()
    {
        $file = new UploadedFile(['file' => __FILE__]);
        $this->assertNull($file->getSize());

        $file = new UploadedFile(['file' => __FILE__, 'size' => 10]);
        $this->assertEquals(10, $file->getSize());
    }

    function testGetError()
    {
        $file = new UploadedFile();
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $file->getError());

        $file = new UploadedFile(['file' => 'unknown_file']);
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $file->getError());

        $file = new UploadedFile(['file' => __FILE__, 'size' => 10]);
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    function testGetClientFilename()
    {
        $file = new UploadedFile();
        $this->assertNull($file->getClientFilename());

        $file = new UploadedFile(['file' => 'unknown_file', 'name' => 'unknown.txt']);
        $this->assertEquals("unknown.txt", $file->getClientFilename());

        $file = new UploadedFile(['file' => __FILE__]);
        $this->assertNull($file->getClientFilename());
    }

    function testGetClientMediaType()
    {
        $file = new UploadedFile();
        $this->assertNull($file->getClientMediaType());

        $file = new UploadedFile(['file' => 'unknown_file', 'type' => 'text/plain']);
        $this->assertEquals("text/plain", $file->getClientMediaType());

        $file = new UploadedFile(['file' => __FILE__]);
        $this->assertNull($file->getClientMediaType());
    }
}
