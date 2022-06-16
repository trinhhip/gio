<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Generator;
use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write as FileWrite;

class FileWriter implements WriterInterface
{
    /**
     * @var DirectoryWrite
     */
    private $directory;

    /**
     * @var FileWrite
     */
    private $stream = false;

    /**
     * @var int|bool
     */
    private $maxFileSize = false;

    /**
     * @var int|bool
     */
    private $maxItemsCount = false;

    /**
     * @var string[]
     */
    private $files = [];

    /**
     * @var null|string
     */
    private $originalPath = null;

    public function __construct(
        Filesystem $filesystem,
        array $writerConfig
    ) {
        $this->directory = $filesystem->getDirectoryWrite(AppDirectoryList::ROOT);
        $this->applyConfig($writerConfig);
    }

    public function open(string $filePath): void
    {
        if ($this->originalPath === null) {
            $this->originalPath = $filePath;
        }
        $absolutePath = $this->getAbsoluteFilePath($filePath);
        $this->stream = $this->directory->openFile($absolutePath);
        $this->files[] = $filePath;
    }

    private function getAbsoluteFilePath(string $filePath): string
    {
        return $this->directory->getAbsolutePath() . trim($filePath, '/');
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function write(Generator $data, array $parts): void
    {
        $itemsCount = 0;

        $header = $parts[self::PART_HEADER];
        $footer = $parts[self::PART_FOOTER];

        $fileSize = $this->stream->write($header);
        foreach ($data as $xmlString) {
            if ($this->maxFileSize !== false) {
                $fileSize += strlen($xmlString);

                if ($this->maxFileSize < $fileSize) {
                    $this->stream->write($footer);
                    $this->changeFile();

                    $itemsCount = $this->maxItemsCount ? 0 : false;
                    $fileSize = $this->stream->write($header);
                    $fileSize += $this->stream->write($xmlString);
                } else {
                    $this->stream->write($xmlString);
                }
            } else {
                $this->stream->write($xmlString);
            }

            if ($this->maxItemsCount !== false) {
                ++$itemsCount;

                if ($this->maxItemsCount <= $itemsCount) {
                    $this->stream->write($footer);
                    $this->changeFile();
                    $itemsCount = 0;
                    $fileSize = $this->stream->write($header);
                }
            }
        }
        $this->stream->write($footer);
        $this->stream->close();
    }

    public function writeIndex(Generator $data, array $parts): void
    {
        $this->open($this->getIndexFilename());
        $this->clearConfig();
        $this->write($data, $parts);
    }

    public function shouldCreateIndexFile(): bool
    {
        return count($this->files) > 1;
    }

    private function changeFile(): void
    {
        $filePath = array_last($this->files);

        if (count($this->files) == 1) {
            $newFilePath = $this->addNumerationToFilename($filePath, 1);
            $this->directory->renameFile(
                $this->getAbsoluteFilePath($filePath),
                $this->getAbsoluteFilePath($newFilePath)
            );
            $this->files[0] = $newFilePath;
        }
        $nextFileIndex = count($this->files) + 1;
        $newFilePath = $this->addNumerationToFilename($this->originalPath, $nextFileIndex);

        $this->stream->close();
        $this->open($newFilePath);
    }

    private function getIndexFilename(): string
    {
        return str_replace('.xml', '_index.xml', $this->originalPath);
    }

    private function addNumerationToFilename(string $fileName, int $index): string
    {
        return str_replace('.xml', sprintf('_%d.xml', $index), $fileName);
    }

    private function applyConfig(array $writerConfig): void
    {
        if ($writerConfig['max_file_size'] > 0) {
            $this->maxFileSize = $writerConfig['max_file_size'] * 1024;
        }

        if ($writerConfig['max_urls'] > 0) {
            $this->maxItemsCount = $writerConfig['max_urls'];
        }
    }

    private function clearConfig(): void
    {
        $this->maxFileSize = false;
        $this->maxItemsCount = false;
    }
}
