<?php

namespace Omnyfy\Approval\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassActions extends \Magento\Ui\Component\MassAction
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    public function __construct(
        ContextInterface $context,
        \Magento\Backend\Model\Session $session,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $components, $data);
        $this->session = $session;
    }

    public function prepare()
    {
        parent::prepare();
        $allowedActions = [];
        $vendorInfo = $this->session->getVendorInfo();
        $config = $this->getConfiguration();
        if (!isset($vendorInfo)) {
            $vendorActions = ['submit_to_review'];
            foreach ($config['actions'] as $action) {
                if (!in_array($action['type'], $vendorActions)) {
                    $allowedActions[] = $action;
                }
            }
        } else {
            $moActions = ['review_passed', 'review_failed'];
            foreach ($config['actions'] as $action) {
                if (!in_array($action['type'], $moActions)) {
                    $allowedActions[] = $action;
                }
            }
        }
        $config['actions'] = $allowedActions;
        $this->setData('config', (array)$config);
    }
}