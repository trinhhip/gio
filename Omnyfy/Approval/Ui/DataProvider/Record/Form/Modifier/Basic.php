<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-21
 * Time: 11:15
 */
namespace Omnyfy\Approval\Ui\DataProvider\Record\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Basic implements ModifierInterface
{
    protected $coreRegistry;

    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->urlBuilder = $urlBuilder;
    }

    public function modifyData(array $data)
    {
        $history = $this->coreRegistry->registry('current_omnyfy_approval_product_history');
        $type = $this->coreRegistry->registry('current_omnyfy_approval_type');
        $productId = $this->coreRegistry->registry('current_omnyfy_approval_product_id');

        $params = [ 'type' => $type ];
        if (!empty($productId)) {
            $params['product'] = $productId;
        }

        $data['config']['submit_url'] = $this->urlBuilder->getUrl('omnyfy_approval/record/save', $params);

        $id = null;
        $data[$id] = $history->getData();
        $data[$id]['history_id'] = $id;
/*
        $data[$id]['history'] = $history->getData();
        $data[$id]['history']['product_name'] = $record->getProductName();
        $data[$id]['history']['vendor_name'] = $record->getVendorName();
        $data[$id]['history']['history_id'] = $id;
*/
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}