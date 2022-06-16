<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Api\Data\TemplateInterfaceFactory;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\Exception\NoSuchEntityException;

class GetById implements GetByIdInterface
{
    /**
     * @var TemplateInterfaceFactory
     */
    private $templateFactory;

    /**
     * @var ConditionalDiscountResource
     */
    private $templateResource;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    public function __construct(
        TemplateInterfaceFactory $templateFactory,
        TemplateResource $templateResource,
        ReadExtensions $readExtensions
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateResource = $templateResource;
        $this->readExtensions = $readExtensions;
    }

    /**
     * @param int $id
     * @return TemplateInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): TemplateInterface
    {
        /** @var TemplateInterface|Template $template */
        $template = $this->templateFactory->create();
        $this->templateResource->load($template, $id);
        if ($template->getId() === null) {
            throw new NoSuchEntityException(
                __('Rule with id "%value" does not exist.', ['value' => $id])
            );
        }

        $this->readExtensions->execute($template);

        return $template;
    }
}
