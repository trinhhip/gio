<?php

namespace Omnyfy\BackendTheme\Model\Config\Backend;

use Magento\MediaStorage\Model\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends \Magento\Config\Model\Config\Backend\Image
{
    const UPLOAD_DIR = 'design/adminhtml/Omnyfy/backend/web/images';
    const ADMIN_BACKEND_LOGO_IMAGE_FILE_NAME = 'magento-icon.svg';
    const ADMIN_LOGIN_SCREEN_IMAGE_FILE_NAME = 'magento-logo.svg';
    const ADMIN_LOGIN_SCREEN_IMAGE_PATH = 'omnyfy_backend/admin_backend/admin_login_screen';
    const ADMIN_BACKEND_LOGO_IMAGE_PATH = 'omnyfy_backend/admin_backend/admin_backend_logo';

    protected function _getUploadDir()
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::APP)
            ->getAbsolutePath(self::UPLOAD_DIR);
    }

    protected function _addWhetherScopeInfo()
    {
        return true;
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        $file = $this->getFileData();
        if (!empty($file) && isset($file['name'])) {
            $uploadDir = $this->_getUploadDir();
            try {
                /** @var Uploader $uploader */
                $uploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $appPath = $this->_filesystem->getDirectoryWrite(DirectoryList::APP);
                switch ($this->getPath()) {
                    case self::ADMIN_LOGIN_SCREEN_IMAGE_PATH:
                        $file['name'] = self::ADMIN_LOGIN_SCREEN_IMAGE_FILE_NAME;
                        break;
                    case self::ADMIN_BACKEND_LOGO_IMAGE_PATH:
                        $file['name'] = self::ADMIN_BACKEND_LOGO_IMAGE_FILE_NAME;
                        break;
                }
                if ($appPath->isExist($uploadDir . '/' . $file['name'])) {
                    $appPath->delete($uploadDir . '/' . $file['name']);
                }
                $result = $uploader->save($uploadDir, $file['name']);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
            }
            $filename = $result['file'];
            if ($filename) {
                if ($this->_addWhetherScopeInfo()) {
                    $filename = $this->_prependScopeInfo($filename);
                }
                $this->setValue($filename);
            }
        } else {
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } elseif (is_array($value) && !empty($value['value'])) {
                $this->setValue($value['value']);
            } else {
                $this->unsValue();
            }
        }

        return $this;
    }

    public function _getAllowedExtensions()
    {
        return ['svg'];
    }
}