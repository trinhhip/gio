<?php
namespace Omnyfy\Mcm\Ui\MassAction;

class PdfInvoices extends \Magento\Ui\Component\MassAction
{
    private $authorization;
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $components,
        array $data
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    public function prepare() {
        parent::prepare();
        $config = $this->getConfiguration();
        $allowedActions = [];
        foreach ($config['actions'] as $action) {
            if ('pdfinvoices_order' != $action['type']) {
                $allowedActions[] = $action;
            }
        }
        $config['actions'] = $allowedActions;
        $this->setData('config', (array)$config);
    }
}