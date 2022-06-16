<?php
namespace Omnyfy\ProductImport\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadImage extends Command
{
    protected $imageHelper;

    public function __construct(
        \Omnyfy\ProductImport\Helper\ProductImage $imageHelper,
        $name = null
    ){
        $this->imageHelper = $imageHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('omnyfy:productimport:downloadimage')
             ->setDescription('Download 100 images for product import');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output){
        $output->writeln('Start downloading images for product import.');
        $this->imageHelper->downloadImages();
        $output->writeln('Finish downloading images for product import.');
    }
}