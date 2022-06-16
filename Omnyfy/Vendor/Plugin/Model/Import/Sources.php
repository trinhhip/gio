<?php

namespace Omnyfy\Vendor\Plugin\Model\Import;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;


class Sources
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    private $helper;

    private $validatedRows;

    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Omnyfy\Vendor\Helper\Data $helper
    )
    {
        $this->backendSession = $backendSession;
        $this->helper = $helper;
    }

    public function afterValidateRow(\Magento\InventoryImportExport\Model\Import\Sources $subject, $result, array $rowData, $rowNum) {
        $currentVendorInfo = $this->backendSession->getVendorInfo();
        if(empty($currentVendorInfo) || isset($this->validatedRows[$rowNum])){
            return $result;
        }
        $this->validatedRows[$rowNum] = true;
        $source_code = $rowData[$subject::COL_SOURCE_CODE];
        $sku= $rowData[$subject::COL_SKU];
        $productVendorId = $this->helper->getVendorIdBySku($sku);
        $vendorId = $this->helper->getVendorIdBySourceCode($source_code);
        if(!empty($vendorId) && $productVendorId != $currentVendorInfo['vendor_id']){
            $this->skipRow($rowNum, 'SKU has been taken by another Vendor', ProcessingError::ERROR_LEVEL_CRITICAL, null, $subject);
            return false;
        }

        if(!empty($vendorId) && $vendorId != $currentVendorInfo['vendor_id']){
            $this->skipRow($rowNum, 'Source code has been taken by another Vendor', ProcessingError::ERROR_LEVEL_CRITICAL, null, $subject);
            return false;
        }
        return $result;
    }

    private function skipRow(
        $rowNum,
        string $errorCode,
        string $errorLevel,
        $colName,
        $subject
    ): self {
        $subject->addRowError($errorCode, $rowNum, $colName, null, $errorLevel);
        $subject->getErrorAggregator()
            ->addRowToSkip($rowNum);
        return $this;
    }

}
