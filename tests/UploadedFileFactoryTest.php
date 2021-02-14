<?php

use IrfanTOOR\Test;
use IrfanTOOR\Http\Stream;
use IrfanTOOR\Http\UploadedFile;
use IrfanTOOR\Http\UploadedFileFactory;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

class UploadedFileFactoryTest extends Test
{
    function testInstance()
    {
        $factory = new UploadedFileFactory();

        $this->assertInstanceOf(UploadedFileFactory::class, $factory);
        $this->assertImplements(UploadedFileFactoryInterface::class, $factory);
    }

    function testCreateUploadedFile()
    {
        $factory = new UploadedFileFactory();

        file_put_contents('test.source', 'hello world! from uploaded file');
        $stream = new Stream(['file' => 'test.source', 'mode' => 'r']);
        $file = $factory->createUploadedFile($stream);
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertImplements(UploadedFileInterface::class, $file);
        $file->moveTo('test.txt');
        $this->assertFile('test.txt');
        $this->assertEquals(
            'hello world! from uploaded file',
            file_get_contents('test.txt')
        );

        unlink('test.txt');
    }
}
