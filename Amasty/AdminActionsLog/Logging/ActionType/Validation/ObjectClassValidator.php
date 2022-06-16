<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Logging\ActionType\Validation;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Util\Ignore\ClassList;

class ObjectClassValidator implements ActionValidatorInterface
{
    /**
     * @var ClassList
     */
    private $classList;

    public function __construct(ClassList $classList)
    {
        $this->classList = $classList;
    }

    public function isValid(MetadataInterface $metadata): bool
    {
        if ($object = $metadata->getObject()) {
            return !$this->classList->isIgnored(get_class($object));
        }

        return true;
    }
}
