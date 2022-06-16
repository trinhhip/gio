<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\SampleData\Policy;

use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\Policy;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Setup;
use Amasty\Gdpr\Model\PolicyFactory;
use Psr\Log\LoggerInterface;

class Installer implements Setup\SampleData\InstallerInterface
{
    const POLICY_FIXTURE_PATH = 'Amasty_Gdpr::fixtures/policy.csv';
    const POLICY_FIXTURE_ENCLOSURE = '|';

    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    private $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvReader;

    /**
     * @var PolicyRepositoryInterface
     */
    private $policyRepository;

    /**
     * @var PolicyFactory
     */
    private $policyFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Setup\SampleData\Context $sampleDataContext,
        PolicyRepositoryInterface $policyRepository,
        PolicyFactory $policyFactory,
        File $file,
        LoggerInterface $logger
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->csvReader->setEnclosure(self::POLICY_FIXTURE_ENCLOSURE);
        $this->policyRepository = $policyRepository;
        $this->policyFactory = $policyFactory;
        $this->file = $file;
        $this->logger = $logger;
    }

    public function install()
    {
        if (!$this->policyRepository->getCurrentPolicy()) {
            try {
                $fixtureFilePath = $this->fixtureManager->getFixture(self::POLICY_FIXTURE_PATH);

                if ($this->file->isExists($fixtureFilePath) && $this->file->isFile($fixtureFilePath)) {
                    $fixtureRows = $this->csvReader->getData($fixtureFilePath);
                    $fixtureHeader = array_shift($fixtureRows);
                    $policyData = array_combine(
                        array_values($fixtureHeader),
                        array_values(reset($fixtureRows))
                    );
                    /** @var Policy $policyModel */
                    $policyModel = $this->policyFactory->create();
                    $policyModel->addData($policyData);
                    $policyModel->setLastEditedBy(null);
                    $policyModel->setStatus(Policy::STATUS_ENABLED);
                    $this->policyRepository->save($policyModel);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
