<?php

namespace Omnyfy\Easyship\Model\EasyshipAccountIdOption;

use Magento\Framework\Data\OptionSourceInterface;
use Omnyfy\Easyship\Model\ResourceModel\EasyshipAccount\CollectionFactory;
use Omnyfy\Vendor\Helper\Backend;

class EasyshipAccountIdOption implements OptionSourceInterface
{
    protected $easyshipAccountIdCollectionFactory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    private $sourceRepository;
    /**
     * @var Backend
     */
    private $backendHelper;


    public function __construct(
        CollectionFactory $easyshipAccountIdCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository,
        Backend $backendHelper
    ) {
        $this->easyshipAccountIdCollectionFactory = $easyshipAccountIdCollectionFactory;
        $this->sourceRepository = $sourceRepository;
        $this->request = $request;
        $this->backendHelper = $backendHelper;
    }

    public function toOptionArray()
    {
        $easyshipAccounts = $this->easyshipAccountIdCollectionFactory->create();
        $sourceCode = $this->request->getParam('source_code');
        if(!empty($sourceCode)){
            $sourceCountry = $this->sourceRepository->get($sourceCode)->getCountryId();
            $easyshipAccounts->addFieldToFilter('country_code', $sourceCountry);
        }else{
            $easyshipAccounts->addFieldToFilter('country_code', 'AU');
        }
        $vendorId = $this->backendHelper->getBackendVendorId();

        $easyshipAccounts->addFieldToFilter(['created_by', 'created_by_mo'], [
            ['eq' => $vendorId],
            ['eq' => 1]
        ]);

        $arrEasyshipAccount = [];
        $arrEasyshipAccount[null] = [
            'value' => null,
            'label' => '-- Select Easyship Account --'
        ];
        foreach ($easyshipAccounts as $account) {
            $arrEasyshipAccount[] = [
                'value' => $account->getId(),
                'label' => $account->getName()
            ];
        }
        return $arrEasyshipAccount;
    }
}
