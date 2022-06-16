<?php

namespace Amasty\RegenerateUrlRewrites\Block\Adminhtml;

use Amasty\RegenerateUrlRewrites\Api\GeneratorInterface;
use Amasty\RegenerateUrlRewrites\Generator\Processing\JobManager;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class StartRegeneration extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_RegenerateUrlRewrites::start-regeneration.phtml';

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var JobManager
     */
    private $jobManager;

    public function __construct(
        LocaleResolver $localeResolver,
        Template\Context $context,
        GeneratorInterface $generator,
        JobManager $jobManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->generator = $generator;
        $this->jobManager = $jobManager;
        $this->setLocale($localeResolver->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getStartUrl()
    {
        return $this->getUrl('amregenerateurlrewrites/command/run', ['store_id' => 1]);
    }

    /**
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->getUrl(
            'amregenerateurlrewrites/command/status',
            ['processIdentity' => 'console_command_regenerate']
        );
    }

    /**
     * @return string
     */
    public function getTerminateUrl()
    {
        return $this->getUrl('amregenerateurlrewrites/command/terminate');
    }

    /**
     * Check if the generation is in progress
     *
     * @return bool
     */
    public function isInProgress()
    {
        $status = $this->generator->getStatus('console_command_regenerate');

        if (($status->getStatus() && in_array($status->getStatus(), ['success', 'failed']))
            || (!$status->getStatus() && $status->getError())
        ) {
            return false;
        }

        if ($pid = $status->getPid()) {
            return $this->jobManager->isPidAlive($pid);
        }

        return true;
    }
}
