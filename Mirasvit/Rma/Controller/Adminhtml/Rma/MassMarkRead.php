<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.25
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Rma\Model\RmaFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Mirasvit\Rma\Controller\Adminhtml\Rma;
use Mirasvit\Rma\Service\Rma\RmaManagement\Save as RmaManagement;
use Magento\Backend\App\Action\Context;

class MassMarkRead extends Rma
{
    protected $filter;

    private $collectionFactory;

    private $rmaFactory;

    private $rmaManagement;

    public function __construct(
        RmaFactory $rmaFactory,
        RmaManagement $rmaManagement,
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->rmaManagement = $rmaManagement;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $rma) {

            if (($this->getRequest()->getParam('is_read'))) {
                $this->rmaManagement->markAsReadForUser($rma);
            } else {
                $this->rmaManagement->markAsUnreadForUser($rma);
            }

        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been changed.', $collectionSize));
        return $resultRedirect->setPath('*/*/index');
    }
}
