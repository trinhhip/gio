<?php
namespace Omnyfy\ProductImport\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignImage extends Command
{
    private $appState;
    protected $imageHelper;

    public function __construct(
        \Magento\Framework\App\State $appState,
        \Omnyfy\ProductImport\Helper\ProductImage $imageHelper,
        $name = null
    ){
        $this->appState = $appState;
        $this->imageHelper = $imageHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('omnyfy:productimport:assignimage')
             ->setDescription('Assign 100 images for product import');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output){
        try{
            $code = $this->appState->getAreaCode();
        }
        catch(\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }

        $output->writeln('Start assigning images for product import.');
        $this->imageHelper->assignImages();
        $output->writeln('Finish assigning images for product import.');
    }
}