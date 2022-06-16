<?php
namespace Omnyfy\Mcm\Plugin\Ui\Component;
class MassAction
{
    private $collectionFactory;
    private $backendUrlManager;
    public function __construct(
        \Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->backendUrlManager = $backendUrlManager;
    }

    public function beforeSetData(\Magento\Ui\Component\MassAction $subject, $key, $value)
    {
        if ($key != 'config') {
            return [$key, $value];
        }

        $config = $value;
        if (isset($config['actions'])) {
            foreach ($config['actions'] as &$item) {
                if ($item['type'] == 'update' && $item['label'] == 'Update Payout Type') {
                    $i = 0;
                    foreach ($this->collectionFactory->create() as $payoutType) {
                        $item['actions'][$i] =
                            [
                                'type' => $payoutType->getId(),
                                'label' => $payoutType->getPayoutType(),
                                'url' => $this->backendUrlManager->getUrl('omnyfy_mcm/payouttype/massUpdate', ['payout_type_id' => $payoutType->getId()])
                            ];
                        $i++;
                    }
                }
            }
        }
        return [$key, $config];
    }
}