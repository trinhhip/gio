<?php

/**
 * Project: Omnyfy Multi Vendor.
 * User: ryan
 * Date: 10/3/2022
 * Time: 10:00 AM
 */

namespace Omnyfy\Vendor\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Resolve error when migrating 2.2 to 2.4
 */
class ResolveMigrate extends Command
{
    const CATALOG_URL_REWRITE = 'catalog_url_rewrite_product_category';
    const DROP_FOREIGN_KEY = 'drop-foreign-key';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        parent::__construct();
        $this->resourceConnection = $resourceConnection;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
    }

    protected function configure()
    {
        $this->setName('omnyfy:migrate:resolve');
        $this->setDescription('Processing resolve');
        $this->addArgument('filename', InputArgument::OPTIONAL, 'Filename ( abcd.csv )');
        $this->addOption(
            self::DROP_FOREIGN_KEY,
            null,
            InputOption::VALUE_OPTIONAL,
            'Drop foreign key'
        );
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('Starting resolve migrate');

        $numberDuplicate = 0;
        $message = '';
        $fileName = $input->getArgument('filename');
        $this->resolveDuplicateData($numberDuplicate, $fileName, $message);

        $output->writeln($message);
        $output->writeln('Removed ' . $numberDuplicate . ' duplicate rows.');
        $output->writeln('Resolve complete.');
    }

    public function getConnection() {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Resolve duplicate data in table catalog_url_rewrite_product_category
     *
     * @return void
     */
    protected function resolveDuplicateData(int &$numberDuplicate, $fileName, &$message)
    {
        $conn = $this->getConnection();
        $selectDistinctData = $conn->select()->from(self::CATALOG_URL_REWRITE)->distinct();
        $selectAll = $conn->select()->from(self::CATALOG_URL_REWRITE, 'url_rewrite_id');
        $data = $conn->fetchAll($selectDistinctData);
        $header[] = ['url_rewrite_id', 'category_id', 'product_id'];
        $data = array_merge($header, $data);
        $countAfterRemoveDuplicate = count($data) - 1;
        $countBeforeRemoveDuplicate = count($conn->fetchCol($selectAll));
        $numberDuplicate = $countBeforeRemoveDuplicate - $countAfterRemoveDuplicate;

        if (!empty($fileName)) {
            $csvHeader = $header[0];
            $fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
            $filePath = $fileDirectoryPath .'/migrate/'. $fileName;
            $csvRows = $this->csvProcessor->getData($filePath);
            $dataImport = [];
            foreach ($csvRows as $row) {
                $current = array_combine($csvHeader, $row);
                $dataImport[] = $current;
            }
            $this->reImportData($dataImport);
            $message = 'Import successful.';
            return;
        }
        $message = $this->exportDataToCsv($data);
        $this->clearData();
        $this->reImportData($data);
    }

    /**
     * Remove all data in table catalog_url_rewrite_product_category
     *
     * @return void
     */
    protected function clearData()
    {
        $conn = $this->getConnection();
        $conn->delete(self::CATALOG_URL_REWRITE);
    }

    /**
     * Re-import data after remove duplicate
     *
     * @param array $data
     *
     * @return void
     */
    protected function reImportData(array $data)
    {
        if (empty($data)) {
            return;
        }

        unset($data[0]);
        $conn = $this->getConnection();
        $conn->insertOnDuplicate(
            self::CATALOG_URL_REWRITE,
            $data
        );
    }

    /**
     * Export data destinct
     *
     * @param array $data
     *
     * @return string
     */
    protected function exportDataToCsv(array $data)
    {
        $fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        $curentTime = str_replace(' ', '-', date('d-m-y h-i-s'));
        $fileName = 'catalog-url-rewrite-' . $curentTime . '.csv';
        $filePath = $fileDirectoryPath .'/migrate/'. $fileName;
        $this->directory->create('migrate');

        /* Pass data array to write in csv file */
        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $data);

        return 'Generated backup file: ' . $filePath;
    }
}
