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
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Service;

class UaService
{
    public function convert(array $data): array
    {
        $auData = [];

        foreach ($data as $eventData) {
            switch ($eventData[1]) {
                case 'view_item':
                    $auData[] = $this->convertViewItem($eventData);
                    break;
                case 'view_item_list':
                    $auData[] = $this->convertViewList($eventData);
                    break;
                case 'add_to_cart':
                    $auData[] = $this->convertAddToCart($eventData);
                    break;
                case 'begin_checkout':
                    $auData[] = $this->convertBeginCheckout($eventData);
                    break;
                case 'purchase':
                    $auData[] = $this->convertPurchase($eventData);
                    break;
            }
        }

        return $auData;
    }

    private function convertViewItem(array $eventData): array
    {
        $data = [
            'event'     => 'productDetail',
            'ecommerce' => [
                'detail' => [
                    'products' => [],
                ],
            ],
        ];

        foreach ($eventData[2]['items'] as $item) {
            $data['ecommerce']['detail']['products'][] = [
                'name'     => $item['item_name'],
                'id'       => $item['item_id'],
                'price'    => $item['price'],
                'currency' => $item['currency'],
                'brand'    => $item['item_brand'],
                'category' => $item['item_category'],
            ];
        }

        return $data;
    }

    private function convertViewList(array $eventData): ?array
    {
        $data = [
            'ecommerce' => [
                'currencyCode' => '',
                'impressions'  => [],
            ],
        ];

        $currency = null;

        foreach ($eventData[2]['items'] as $item) {
            $currency = $item['currency'];

            $data['ecommerce']['impressions'][] = [
                'name'     => $item['item_name'],
                'id'       => $item['item_id'],
                'price'    => $item['price'],
                'currency' => $item['currency'],
                'brand'    => $item['item_brand'],
                'category' => $item['item_category'],
            ];
        }

        $data['ecommerce']['currencyCode'] = $currency;

        return $data;
    }

    private function convertAddToCart(array $eventData): array
    {
        $data = [
            'event'     => 'addToCart',
            'ecommerce' => [
                'currencyCode' => $eventData[2]['currency'],
                'add'          => [
                    'products' => [],
                ],
            ],
        ];

        foreach ($eventData[2]['items'] as $item) {
            $data['ecommerce']['add']['products'][] = [
                'name'     => $item['item_name'],
                'id'       => $item['item_id'],
                'price'    => $item['price'],
                'brand'    => $item['item_brand'],
                'quantity' => $item['quantity'],
            ];
        }

        return $data;
    }

    private function convertBeginCheckout(array $eventData): array
    {
        $data = [
            'event'     => 'checkout',
            'ecommerce' => [
                'checkout' => [
                    'actionField' => ['step' => 'begin', 'option' => __('Begin')],
                    'products'    => [],
                ],
            ],
        ];

        foreach ($eventData[2]['items'] as $item) {
            $data['ecommerce']['checkout']['products'][] = [
                'name'     => $item['item_name'],
                'id'       => $item['item_id'],
                'price'    => $item['price'],
                'brand'    => $item['item_brand'],
                'quantity' => $item['quantity'],
            ];
        }

        return $data;
    }

    private function convertPurchase(array $eventData): array
    {
        $data = [
            'ecommerce' => [
                'purchase' => [
                    'actionField' => [
                        'id'      => $eventData[2]['transaction_id'],
                        'revenue' => $eventData[2]['value'],
                    ],
                    'products'    => [],
                ],
            ],
        ];

        foreach ($eventData[2]['items'] as $item) {
            $data['ecommerce']['purchase']['products'][] = [
                'name'     => $item['item_name'],
                'id'       => $item['item_id'],
                'price'    => $item['price'],
                'brand'    => $item['item_brand'],
                'quantity' => $item['quantity'],
            ];
        }

        return $data;
    }
}
