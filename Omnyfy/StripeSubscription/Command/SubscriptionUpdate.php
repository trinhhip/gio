<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-08-08
 * Time: 14:45
 */
namespace Omnyfy\StripeSubscription\Command;

use Omnyfy\Core\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Omnyfy\VendorSubscription\Model\Source\SubscriptionStatus;

class SubscriptionUpdate extends Command
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $_appState;

    protected $_storeManager;

    protected $_qHelper;

    protected $_helper;

    protected $_eventManager;

    protected $_requiredFields = [
        'id',
        'customer',
        'status',
    ];

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Core\Helper\Queue $queueHelper,
        \Omnyfy\StripeSubscription\Helper\Data $helper,
        \Magento\Framework\Event\Manager $eventManager,
        string $name = null)
    {
        $this->_appState = $state;
        $this->_storeManager = $storeManager;
        $this->_qHelper = $queueHelper;
        $this->_helper = $helper;
        $this->_eventManager = $eventManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('omnyfy:stripe:subscription_update');
        $this->setDescription('Process subscription updated event');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('Previous instance of this command still running.');
            return;
        }
        try {
            $code = $this->_appState->getAreaCode();
            $output->writeln('Running in area: ' . $code);
        } catch (\Exception $e) {
            $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }

        $output->writeln('Start to process');

        $i = $done = $failed = $invalid = 0;

        $itemId = null;
        $eventIdList = [];
        while($qItem = $this->_qHelper->takeDataFromQueue('stripe_subscription_updated', $itemId)) {
            $i++;
            $contentId = $qItem['content_id'];

            $content = $this->_helper->getContentById($contentId);
            if (empty($content)) {
                $this->_qHelper->updateQueueMsgStatus($itemId, 'blocking');
                $invalid++;
                continue;
            }

            $dataError = false;
            foreach($this->_requiredFields as $field) {
                if (!isset($content['data']['object'][$field]) || empty($content['data']['object'][$field])) {
                    $dataError = true;
                    break;
                }
            }

            if ($dataError) {
                $this->_qHelper->updateQueueMsgStatus($itemId, 'error');
                $failed++;
                continue;
            }
            $eventId = $content['id'];
            if(in_array($eventId, $eventIdList)){
                $this->_qHelper->updateQueueMsgStatus($itemId, 'skip');
                continue;
            }
            try {
                $data = [
                    'gateway_id' => $content['data']['object']['id'],
                    'status' => $this->_helper->convertStatus($content['data']['object']),
                    'next_billing_at' => date(self::DATE_FORMAT, $content['data']['object']['current_period_end']),
                    'cancelled_at' => date(self::DATE_FORMAT, $content['data']['object']['canceled_at']),
                    'expiry_at' => date(self::DATE_FORMAT, $content['data']['object']['current_period_end']),
                    'plan_gateway_id' => $content['data']['object']['plan']['id'],
                    'is_cancelled' => $content['data']['object']['cancel_at_period_end']
                ];

                //Retrieve customer email by customer Id
                $customerInfo = $this->_helper->retrieveCustomer($content['data']['object']['customer']);
                $data['email'] = $customerInfo['email'];
                $data['customer_gateway_id'] = $content['data']['object']['customer'];

                $this->_eventManager->dispatch('omnyfy_subscription_updated', [
                    'data' => $data,
                ]);

                $this->_qHelper->updateQueueMsgStatus($itemId, 'done');
                $done++;
                $eventIdList[] = $eventId;
            }
            catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $this->_qHelper->updateQueueMsgStatus($itemId, 'error');
                $failed++;
            }
        }

        $output->writeln('Done. Got '. $i . ' items in total.');
        $output->writeln('Invalid items: '. $invalid);
        $output->writeln('Succeeded: '. $done);
        $output->writeln('Failed: ' . $failed);
    }
}
