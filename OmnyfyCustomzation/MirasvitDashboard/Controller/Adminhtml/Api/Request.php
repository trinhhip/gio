<?php

namespace OmnyfyCustomzation\MirasvitDashboard\Plugin\Mirasvit\Dashboard\Controller\Adminhtml\Api;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Model\Block;
use Mirasvit\Dashboard\Repository\BoardRepository;
use Mirasvit\Dashboard\Service\BlockService;
use Mirasvit\Report\Api\Service\CastingServiceInterface;
use Mirasvit\Report\Controller\Adminhtml\Api\AbstractApi;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;
use Zend_Json;

/**
 * Class Request
 *
 * @package OmnyfyCustomzation\MirasvitDashboard\Plugin\Mirasvit\Dashboard\Controller\Adminhtml\Api
 */
class Request extends AbstractApi
{
    /**
     * @var BoardRepository
     */
    private $boardRepository;

    /**
     * @var CastingServiceInterface
     */
    private $castingService;

    /**
     * @var BlockService
     */
    private $blockService;

    /**
     * @var VendorResource
     */
    private $vendorResource;
    private Session $session;

    /**
     * Request constructor.
     *
     * @param VendorResource $vendorResource
     * @param BoardRepository $boardRepository
     * @param CastingServiceInterface $castingService
     * @param BlockService $blockService
     * @param Action\Context $context
     * @param Session $session
     */
    public function __construct(
        VendorResource $vendorResource,
        BoardRepository $boardRepository,
        CastingServiceInterface $castingService,
        BlockService $blockService,
        Action\Context $context,
        Session $session
    )
    {
        $this->vendorResource = $vendorResource;
        $this->boardRepository = $boardRepository;
        $this->castingService = $castingService;
        $this->blockService = $blockService;
        $this->session = $session;

        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $token = $this->getRequest()->getParam('token');

        /** @var BoardInterface $board */
        $board = $this->boardRepository->getCollection()
            ->addFieldToFilter(BoardInterface::MOBILE_TOKEN, $token)
            ->addFieldToFilter(BoardInterface::IS_MOBILE_ENABLED, true)
            ->getFirstItem();

        if ($board->getId()) {
            /** @var \Magento\Framework\App\Request\Http $request */
            $request->setDispatched(true);
            $request->setActionName('request');
        }

        return parent::dispatch($request);
    }

    /**
     * @return Http|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Http $jsonResponse */
        $jsonResponse = $this->getResponse();

        try {
            $params = $this->castingService->toUnderscore($this->getRequest()->getParams());

            if ($currentAdminUser = $this->session->getUser()) {
                $custFilter = $params['block']['config']['filters'];
                $vendorId = $this->vendorResource->getVendorIdByUserId($currentAdminUser->getId());
                if ($vendorId) {
                    foreach ($custFilter as $k => $filter) {
                        if (stripos($filter['column'], 'vendor_id')) {
                            unset($custFilter[$k]);
                        }
                    }
                    array_push($custFilter,
                        [
                            "column" => "sales_order_item|vendor_id",
                            "condition_type" => "eq",
                            "value" => $vendorId
                        ]
                    );

                    $params['block']['config']['filters'] = $custFilter;
                }
            }

            $block = new Block($params['block']);
            $response = $this->blockService->getApiResponse($block, $params['filters']);

            $responseData = $response->toArray();
            return $jsonResponse->representJson(Zend_Json::encode([
                'success' => true,
                'data' => $responseData,
            ]));
        } catch (Exception $e) {
            return $jsonResponse->representJson(Zend_Json::encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
        }
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }
}
