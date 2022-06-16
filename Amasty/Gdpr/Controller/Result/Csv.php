<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Result;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

class Csv extends AbstractResult
{
    const CSV = 'csv';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @var string
     */
    private $fileName;

    public function __construct(
        File $fileDriver,
        string $fileName = 'data.csv'
    ) {
        $this->fileDriver = $fileDriver;
        $this->fileName = $fileName;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    protected function render(HttpResponseInterface $response)
    {
        $this->setHeaders($response);
        $response->setContent($this->generateContent());

        return $this;
    }

    protected function setHeaders(HttpResponseInterface $response)
    {
        $response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'text/csv', true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->fileName . '"', true)
            ->setHeader('Last-Modified', date('r'), true);
    }

    protected function generateContent(): string
    {
        $resource = $this->fileDriver->fileOpen('php://memory', 'w');

        foreach ($this->data as $row) {
            $this->fileDriver->filePutCsv($resource, $row);
        }

        $fileSize = $this->fileDriver->fileTell($resource);
        $this->fileDriver->fileSeek($resource, 0);

        return $this->fileDriver->fileRead($resource, $fileSize);
    }
}
