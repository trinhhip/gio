<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;
    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory
     */
    private $vendorCollectionFactory;
    /**
     * @var \Omnyfy\Vendor\Api\VendorAttributeRepositoryInterface
     */
    private $vendorAttributeRepository;
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory,
        \Omnyfy\Vendor\Api\VendorAttributeRepositoryInterface $vendorAttributeRepository,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    )
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->vendorAttributeRepository = $vendorAttributeRepository;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    public function execute()
    {
        try {
            $filepath = 'export/vendorList.csv';
            $this->directory->create('export');
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $vendorCollection = $this->vendorCollectionFactory->create()->addFieldToSelect('*');
            $vendorCollection->getSelect()->joinLeft(
                ['admin_user' => 'admin_user'],
                'admin_user.email = e.email',
                ['last_login_date' => 'logdate']
            );
            $userCollection = $this->userCollectionFactory->create()->addFieldToSelect('*');
            $firstVendorData = $vendorCollection->getData()[0];
            $firstVendorItem = $vendorCollection->getFirstItem();
            $header = [];
            foreach ($firstVendorData as $key => $value) {
                $header[] = $this->getLabelColumn($firstVendorItem, $key);
            }
            // foreach ($customAttributes = $this->vendorAttributeRepository->getCustomAttributesMetadata() as $customAttribute) {
            //     if (!in_array($customAttribute->getAttributeCode(), array_keys($firstVendorData))) {
            //         $header[] = $customAttribute->getFrontendLabel();
            //     }
            // }
            $header[] = 'Subvendors';
            $header[] = 'Subvendor Email';
            $header[] = 'Subvendor Phone Number';
            $header[] = 'Subvendor Status';
            $stream->writeCsv($header);
            foreach ($vendorCollection->getData() as $key => $vendor) {
                $data = [];
                $subvendors = [];
                $subvendorName = [];
                $subvendorEmail = [];
                $subvendorPhone = [];
                $subvendorStatus = [];
                foreach ($vendor as $vendorKey => $value) {
                    $data[] = $this->getValue($vendorCollection->getItems()[$key + 1], $vendorKey, $value);
                }
                // foreach ($customAttributes as $customAttribute) {
                //     if (!in_array($customAttribute->getAttributeCode(), array_keys($firstVendorData))) {
                //         if (in_array($customAttribute->getAttributeCode(), array_keys($vendorCollection->getItems()[$key + 1]->getData()))) {
                //             $data[] = $this->getValue($vendorCollection->getItems()[$key + 1], $customAttribute->getAttributeCode(), $vendorCollection->getItems()[$key + 1]->getData($customAttribute->getAttributeCode()));
                //         } else {
                //             $data[] = '';
                //         }
                //     }
                // }
                foreach ($userCollection->getItems() as $user) {
                    if ($user->getParentVendorId() != 0 && $user->getParentVendorId() == $vendorCollection->getItems()[$key + 1]->getId()) {
                        $subvendorName[] = $user->getName() . "\n";
                        $subvendorEmail[] = $user->getEmail() . "\n";
                        $subvendorPhone[] = $vendorCollection->getItems()[$key + 1]->getPhone() . "\n";
                        $subvendorStatus[] = $user->getIsActive() . "\n";
                    }
                }
                $data[] = implode("", $subvendorName);
                $data[] = implode("", $subvendorEmail);
                $data[] = implode("", $subvendorPhone);
                $data[] = implode("", $subvendorStatus);
                $stream->writeCsv($data);
            }

            $downloadedFileName = 'VendorList.csv';
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            return $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            throw new \Exception(__('Some thing went wrong when export'));
        }
    }

    public function shouldGenerateColumn($item, $label)
    {
        if ($item->getResource()->getAttribute($label) && $item->getResource()->getAttribute($label)->getFrontendLabel()) {

            $labelsToSkip = ['Logo', 'Banner', 'Shipping Policy', 'Return Policy', 'Payment Policy', 'Marketing Policy', 'Booking Lead Time', 'Project Types'];
            if (in_array($item->getResource()->getAttribute($label)->getFrontendLabel(), $labelsToSkip)) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public function getLabelColumn($item, $label)
    {
        if ($item->getResource()->getAttribute($label) && $item->getResource()->getAttribute($label)->getFrontendLabel()) {
            return $item->getResource()->getAttribute($label)->getFrontendLabel();
        } else {
            if (strpos($label, '_') !== false) {
                return ucwords(str_replace('_', ' ', $label));
            } else {
                return ucwords($label);
            }
        }
    }

    public function getValue($vendor, $attributeCode, $value)
    {
        $attribute = $vendor->getResource()->getAttribute($attributeCode);
        if ($attribute && $attribute->getIsVisibleOnFront() !== null && $attribute->getUsedInListing() !== null) {
            if ($vendor) {
                $skipAttributes = ['logo', 'banner', 'shipping_policy', 'return_policy', 'payment_policy', 'marketing_policy', 'booking_lead_time', 'project_types'];

                if (in_array($attribute->getAttributeCode(), $skipAttributes)) {
                    return '';
                }
                $customerAttribute = $vendor->getData($attribute->getAttributeCode());
                $value = $vendor->getResource()->getAttribute($attribute)->getFrontEnd()->getValue($vendor);
                if ($customerAttribute !== null) {
                    if ($attribute->getFrontendInput() == "text" || $attribute->getFrontendInput() == "textarea" || $attribute->getFrontendInput() == "image") {
                        return $value;
                    } else if ($attribute->getFrontendInput() == "select") {
                        if (is_string($value)) {
                            return $value;
                        } else {
                            return $value->getText();
                        }
                    } else if ($attribute->getFrontendInput() == "multiselect") {
                        return $customerAttribute;
                    }
                }
            }
        } else {
            return $value;
        }
        return '';
    }
}