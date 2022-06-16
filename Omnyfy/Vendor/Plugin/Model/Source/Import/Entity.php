<?php


namespace Omnyfy\Vendor\Plugin\Model\Source\Import;


use Magento\Backend\Model\Auth\Session as AuthSession;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class Entity
{
    const ALLOW_IMPORT = ['','catalog_product','stock_sources','omnyfy_vendor_inventory','omnyfy_shipping_distance_rate'];
    /**
     * @var VendorResource
     */
    private $vendorResource;
    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * Entity constructor.
     * @param VendorResource $vendorResource
     * @param AuthSession $authSession
     */
    public function __construct(
          VendorResource $vendorResource,
          AuthSession $authSession
      )
      {
          $this->vendorResource = $vendorResource;
          $this->authSession = $authSession;
      }

    /**
     * @param \Magento\ImportExport\Model\Source\Import\Entity $subject
     * @param array $result
     * @return array
     */
    public function afterToOptionArray(\Magento\ImportExport\Model\Source\Import\Entity $subject, array $result): array
    {
        if(!$this->checkAdminIsVendor()){
            return $result;
        } else {
            $options = [];
            foreach ($result as $option){
                if(in_array($option['value'], self::ALLOW_IMPORT)){
                    $options[] = ['label' =>$option['label'], 'value' => $option['value']];
                }
            }
            return $options;
        }
    }

    private function checkAdminIsVendor(){
        $userId = $this->authSession->getUser()->getId();
        return $this->vendorResource->getVendorIdByUserId($userId);
    }

}
