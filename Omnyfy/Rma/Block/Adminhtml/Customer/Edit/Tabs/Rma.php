<?php

namespace Omnyfy\Rma\Block\Adminhtml\Customer\Edit\Tabs;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;

class Rma extends Widget implements TabInterface
{

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('RMA');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('RMA');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getId()) {
            return '';
        }
        $id = $this->getId();
        $rmaNewUrl = $this->getUrl('rma/rma/add', ['customer_id' => $id]);
        $sortParam = $this->getRequest()->getParam('sort');

        $button = !empty($sortParam) ? '' : $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\'' . $rmaNewUrl . '\'')
            ->setLabel(__('Create RMA'))
            ->toHtml();

        /** @var \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid */
        $grid = $this->getLayout()->createBlock('\Omnyfy\Rma\Block\Adminhtml\Rma\Grid');
        $grid->addCustomFilter('main_table.customer_id', $id);
        $grid->setFilterVisibility(false);
        $grid->setExportVisibility(false);
        $grid->setPagerVisibility(false);
        $grid->setTabMode(true);

        return '<div>' . $button . $grid->toHtml() . '</div>';
    }
}