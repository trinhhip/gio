<?php
namespace Omnyfy\Easyship\Ui\DataProvider\Source\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class GrantPermissionPool implements ModifierInterface
{
    protected $_scopeConfig;
    protected $session;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Backend\Model\Session $session
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->session = $session;
    }

    public function modifyData(array $data) {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
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

        $isEasyshipShippingActive = (int)$this->isEasyshipEnabled();

        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => [
                        'easyship_account_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $isEasyshipShippingActive,
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

        $meta = array_replace_recursive(
            $meta,
            [
                'general' => [
                    'children' => [
                        'easyship_address_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $isEasyshipShippingActive,
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

        $meta = array_replace_recursive(
            $meta,
            [
                'contact_info' => [
                    'children' => [
                        'company_name' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'visible' => $isEasyshipShippingActive,
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

    public function isEasyshipEnabled(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('carriers/easyship/active', $storeScope);
        return $enable;
    }
}