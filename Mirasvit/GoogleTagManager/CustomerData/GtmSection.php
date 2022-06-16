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

namespace Mirasvit\GoogleTagManager\CustomerData;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\UaService;

class GtmSection implements SectionSourceInterface
{
    private $customerSession;

    private $dataLayer;

    private $groupRepository;

    private $uaService;

    public function __construct(
        CustomerSession $customerSession,
        DataLayer $dataLayer,
        GroupRepositoryInterface $groupRepository,
        UaService $uaService
    ) {
        $this->dataLayer       = $dataLayer;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->uaService       = $uaService;
    }

    public function getSectionData()
    {
        $data = [];

        if ($storedData = $this->dataLayer->getCheckoutData()) {
            $this->dataLayer->resetCheckoutData();

            $data = array_merge($data, $storedData, $this->uaService->convert($storedData));
        }

        if ($this->customerSession->getId()) {
            $customer = $this->customerSession->getCustomer();

            $groupName = '';

            try {
                $group = $this->groupRepository->getById($customer->getGroupId());
                if ($group) {
                    $groupName = $group->getCode();
                }
            } catch (\Exception $e) {
            }

            $data[] = [
                'event'          => 'set_user_properties',
                'user_id'        => $customer->getId(),
                'email'          => $customer->getEmail(),
                'customer_group' => $groupName,
            ];
        } else {
            $data[] = [
                'event'          => 'set_user_properties',
                'customer_group' => 'NOT LOGGED IN',
            ];
        }

        return [
            'push' => $data,
        ];
    }
}
