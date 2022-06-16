<?php
namespace Omnyfy\Mcm\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class DeleteDirectory
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var WriteInterface
     */
    protected $deleteDirectory;

    public function __construct(
        Filesystem $fileSystem
    ) {
        $this->directory = $fileSystem->getDirectoryWrite(DirectoryList::PUB);
    }

    /**
     * Delete folder
     *
     * @return bool
     * @throws LocalizedException
     */
    public function deleteDirectory($mediaDirectory)
    {
        $this->directory->delete($mediaDirectory);
    }
}