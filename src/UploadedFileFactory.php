<?php

namespace IrfanTOOR\Http;

use IrfanTOOR\Http\UploadedFileFactory;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;


class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * "If a size is not provided it will be determined by checking the size of
     * the file. " UploadedFileFactoryInterface.
     *
     * NOTE: this is insane as createUploadedFile method uses a stream for creating
     * an instance of UploadedFile, which, even if provides the file, does not mean
     * that the file could be accessible, or is on the same system.
     */
    public function createUploadedFile(
            StreamInterface $stream,
            int $size = null,
            int $error = \UPLOAD_ERR_OK,
            string $clientFilename = null,
            string $clientMediaType = null
        ): UploadedFileInterface
    {
        if (!$stream->isReadable())
            throw new \InvalidArgumentException("Resource is not readable.");

            return new UploadedFile([
            'file' => $stream->getMetaData('uri') ?? null, # todo -- check the meta-data => resource file
            'name' => $clientFilename,
            'type' => $clientMediaType,
            'size' => $size ?? $stream->getSize(),
            'error' => $error
        ]);
    }
}
