<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Account\Edit;

class GenericButton
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Omnyfy\Easyship\Model\EasyshipAccountFactory
     */
    protected $account;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory
    ) {
        $this->context = $context;
        $this->accountFactory = $accountFactory;
    }

    /**
     * Return CMS page ID
     *
     * @return int|null
     */
    public function getAccountId()
    {
        try {
            $accountId = $this->context->getRequest()->getParam('id');
            return $this->accountFactory->create()->load($accountId)->getData('entity_id');
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
