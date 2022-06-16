<?php

namespace Omnyfy\Core\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

class SaveMultipleEmail extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * SaveMultipleEmail constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct
    (
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return SaveMultipleEmail
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim($this->getValue());

        if ($value == ''){
            return $this;
        }

        $listErrChar = [' ', ';', '!', '#', '!', '$', '%', '^', '*', '#', '&', '(', ')', '~', '{', '}', '_','\'', '/', '`', '=', '|', '+'];
        for ($i = 0; $i < count($listErrChar); $i++){
            $value = str_replace($listErrChar[$i], '', $value);

        }
        $regex = "/^([\w+-.%]+@[\w.-]+\.[A-Za-z]{2,4})(,[\w+-.%]+@[\w.-]+\.[A-Za-z]{2,4})*$/";
        if (!preg_match($regex, $value)) {
            throw new LocalizedException(__('Wrong email list. Correct format example: roni_cost@example.com,roni_cost123@example.com'));
        }
        $this->setValue($value);
        return $this;
    }
}