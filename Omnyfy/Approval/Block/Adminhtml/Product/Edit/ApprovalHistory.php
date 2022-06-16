<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 15:38
 */
namespace Omnyfy\Approval\Block\Adminhtml\Product\Edit;

class ApprovalHistory extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $collectionFactory;

    protected $statusSource;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Omnyfy\Approval\Model\Resource\History\CollectionFactory $collectionFactory,
        \Omnyfy\Approval\Model\Source\Status $status,
        array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        $this->statusSource = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('approvalGrid');
    }

    protected function _prepareCollection()
    {
        $id = $this->getParam('id');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('product_id', $id);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header' => __('ID'),
                'index' => 'history_id'
            ]
        );
        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'index' => 'comment'
            ]
        );
        $this->addColumn(
            'before_status',
            [
                'header' => __('Before Status'),
                'index' => 'before_status',
                'type' => 'options',
                'options' => $this->statusSource->toValuesArray(),
            ]
        );
        $this->addColumn(
            'after_status',
            [
                'header' => __('After Status'),
                'index' => 'after_status',
                'type' => 'options',
                'options' => $this->statusSource->toValuesArray()
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Date'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        return parent::_prepareColumns();
    }
}
