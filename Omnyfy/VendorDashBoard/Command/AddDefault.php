<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 29/01/2020
 * Time: 5:20 PM
 */

namespace Omnyfy\VendorDashBoard\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class AddDefault extends \Omnyfy\Core\Command\Command
{
    const USER_ID_MARKET = 'idMarket';

    const USER_ID_DEFAULT = 'idDefault';

    protected $appState;

    protected $helperData;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Omnyfy\VendorDashBoard\Helper\Data $helperData,
        $name = null
    )
    {
        $this->appState = $state;
        $this->helperData = $helperData;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('omnyfy:add:dashboard');
        $this->setDescription('Automatic generate default of vendor dashboard');
        $this->addOption(
            self::USER_ID_MARKET,
            null,
            InputOption::VALUE_REQUIRED,
            'USER ID MARKET'
        );
        $this->addOption(
            self::USER_ID_DEFAULT,
            null,
            InputOption::VALUE_REQUIRED,
            'USER ID DEFAULT'
        );
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            return;
        }
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }

        try {
            $userId1 = $input->getOption(self::USER_ID_MARKET);
            $userId2 = $input->getOption(self::USER_ID_DEFAULT);
            if ($userId1 && $userId2) {
                $output->writeln('Start');

                $this->helperData->addDefaultDashBoards($userId1, $userId2);

                $output->writeln('Done');
            } else {
                $output->writeln('Error');
            }


        } catch (\Exception $exception){
        }
    }
}
