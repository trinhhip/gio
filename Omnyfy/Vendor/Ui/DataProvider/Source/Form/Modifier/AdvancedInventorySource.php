<?php
namespace Omnyfy\Vendor\Ui\DataProvider\Source\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Listing\Columns\Column;

class AdvancedInventorySource implements ModifierInterface
{
    protected $session;

    public function __construct(
        \Magento\Backend\Model\Session $session
    ) {
        $this->session = $session;
    }

    public function modifyData(array $data) {
        return $data;
    }

    public function modifyMeta(array $meta) {
        $vendorInfo = $this->session->getVendorInfo();
        if (!empty($vendorInfo)) {
            $isVisiable = 0;
        } else {
            $isVisiable = 1;
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => [
                        'vendor_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $isVisiable
                                    ]
                                ],
                            ],
                        ]
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                            ]
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }
}