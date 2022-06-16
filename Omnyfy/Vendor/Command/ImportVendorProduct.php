<?php
 
namespace Omnyfy\Vendor\Command;
 
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\Import\Source\CsvFactory;
use Magento\Framework\Filesystem\Directory\ReadFactory;
 
/**
 * Command to import products.
 */
class ImportVendorProduct extends Command
{
    /**
     * @var State $state
     */
    private $state;
 
    /**
     * @var Import $importFactory
     */
    protected $importFactory;
 
    /**
     * @var CsvFactory
     */
    private $csvSourceFactory;
 
    /**
     * @var ReadFactory
     */
    private $readFactory;
 
    /**
     * Constructor
     *
     * @param State $state  A Magento app State instance
     * @param ImportFactory $importFactory Factory to create entiry importer
     * @param CsvFactory $csvSourceFactory Factory to read CSV files
     * @param ReadFactory $readFactory Factory to read files from filesystem
     *
     * @return void
     */
    public function __construct(
      State $state,
      ImportFactory $importFactory,
      CsvFactory $csvSourceFactory,
      ReadFactory $readFactory
    ) {
        $this->state = $state;
        $this->importFactory = $importFactory;
        $this->csvSourceFactory = $csvSourceFactory;
        $this->readFactory = $readFactory;
        parent::__construct();
    }
 
    /**
     * Configures arguments and display options for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('omnyfy:vendor:import_vendor_products');
        $this->setDescription('Imports Vendor Products from a CSV');
        $this->addArgument('import_path', InputArgument::REQUIRED, 'The path of the import file (ie. ../../path/to/file.csv)');
        $this->addArgument('validation_strategy', InputArgument::REQUIRED, 'Options: validation-stop-on-errors, validation-skip-errors');
        $this->addArgument('behavior', InputArgument::OPTIONAL, 'Options: append, replace, delete');
        parent::configure();
    }
 
    /**
     * Executes the command to add products to the database.
     *
     * @param InputInterface  $input  An input instance
     * @param OutputInterface $output An output instance
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import_path = $input->getArgument('import_path');
        $output->writeln("<info>Start importing products from $import_path</info>");
        $behavior = $input->getArgument('behavior');
        $validate = $input->getArgument('validation_strategy');
        $import_file = pathinfo($import_path);
        if ($validate != 'validation-stop-on-errors' && $validate != 'validation-skip-errors') {
            $output->writeln("<error>Wrong Validation Strategy. You have to input one in two options: validation-stop-on-errors, validation-skip-errors</error>");
            return;
        }
        if ($behavior != 'append' && $behavior != 'replace' && $behavior != 'delete' && $behavior != null) {
            $output->writeln("<error>Wrong behavior. You have to input one in three option: append, replace, delete.</error>"); 
            return;
        }

        // We cannot use core functions (like saving a product) unless the area
        // code is explicitly set.
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Intentionally left empty.
        }

        $import = $this->importFactory->create();
        $import->setData(
            array(
                'entity' => 'catalog_product',
                'behavior' => empty($behavior) ? 'append' : $behavior,
                'validation_strategy' => $validate,
            )
        );
 
        $read_file = $this->readFactory->create($import_file['dirname']);
        $csvSource = $this->csvSourceFactory->create(
            array(
                'file' => $import_file['basename'],
                'directory' => $read_file,
            )
        );
 
        $validate = $import->validateSource($csvSource);
        if (!$validate) {
          $output->writeln('<error>Unable to validate the CSV.</error>');
        }
 
        $result = $import->importSource();
        if ($result) {
          $import->invalidateIndex();
        }
 
        $output->writeln("<info>Finished importing products from $import_path</info>");
    }
}