<?php
namespace Omnyfy\Webhook\Model;
use Omnyfy\Webhook\Api\Data\WebhookTypeInterface;

class WebhookType extends \Magento\Framework\Model\AbstractModel implements WebhookTypeInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Webhook\Model\ResourceModel\WebhookType');
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param int $type
     * @return \Omnyfy\Webhook\Api\Data\WebhookTypeInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }
}