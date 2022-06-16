<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Console\Command\Regenerate;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputInterface;

class InputValidator
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param InputInterface $input
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate(InputInterface $input): void
    {
        $this->checkStoreInput($input->getOption(OptionResolverInterface::INPUT_KEY_STORE_ID));
        $this->validateEntityIdsOptions($input);
    }

    /**
     * @param $storeId
     * @return void
     * @throws InvalidArgumentException
     */
    private function checkStoreInput($storeId): void
    {
        if (!$storeId) {
            return;
        }

        if (strlen($storeId) && ctype_digit($storeId)) {
            try {
                $this->storeManager->getStore($storeId);
            } catch (NoSuchEntityException $e) {
                throw new InvalidArgumentException('ERROR: store with this ID not exists.');
            }
        } else {
            throw new InvalidArgumentException('ERROR: store ID should have an integer value.');
        }
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    private function validateEntityIdsOptions(InputInterface $input): void
    {
        $this->validateIdsRangeOption($input->getOption(OptionResolverInterface::INPUT_KEY_IDS_RANGE));
        $this->validateSpecificIdsOption($input->getOption(OptionResolverInterface::INPUT_KEY_SPECIFIC_IDS));
    }

    public function validateSpecificIdsOption(?string $specificIds): void
    {
        if ($specificIds && !preg_match('/^[0-9]+(,[0-9]+)*$/', $specificIds)) {
            throw new InvalidArgumentException('ERROR: ids should be like 1,2,3.');
        }
    }

    public function validateIdsRangeOption(?string $idsRange): void
    {
        if ($idsRange && !preg_match('/^[0-9]+-[0-9]+$/', $idsRange)) {
            throw new InvalidArgumentException('ERROR: ids-range should be like 10-123');
        }
    }
}
