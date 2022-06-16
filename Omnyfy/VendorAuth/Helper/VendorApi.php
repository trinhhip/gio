<?php

namespace Omnyfy\VendorAuth\Helper;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Framework\Webapi\Rest\Request as RestRequest;

class VendorApi extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENDPOINT_TYPE_REST_API = 'REST API';
    const ENDPOINT_TYPE_GRAPHQL = 'GraphQL';

    /**
     * @var TokenCollectionFactory
     */
    protected $tokenModelCollectionFactory;

    /**
     * @var \Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist\CollectionFactory
     */
    protected $endpointCollectionFactory;

    protected $restRequest;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist\CollectionFactory $endpointCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TokenCollectionFactory $tokenModelCollectionFactory,
        \Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist\CollectionFactory $endpointCollectionFactory,
        RestRequest $restRequest
    ){
        parent::__construct($context);
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->endpointCollectionFactory = $endpointCollectionFactory;
        $this->restRequest = $restRequest;
    }

    /**
     * Return null if API doesn't have Authorization Header or doesn't have bearer token or token is not correct
     * Return 0 if token is correct but it is not vendor's token
     * Return vendor_id if it is vendor's token
     * @return int|null
     */
    public function getVendorIdFromToken() {
        $authorizationHeaderValue = $this->restRequest->getHeader('Authorization');
        //Doesn't have authorization header
        if (!$authorizationHeaderValue) {
            return null;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        //Header values doesn't map with bearer format
        if (count($headerPieces) !== 2) {
            return null;
        }

        $tokenType = strtolower($headerPieces[0]);
        // Not bearer token
        if ($tokenType !== 'bearer') {
            return null;
        }

        $token = $headerPieces[1];
        $collection = $this->tokenModelCollectionFactory->create()
            ->addFieldToFilter('token', ['like' => $token]);

        //Token Is not valid
        if ($collection->getSize() == 0) {
            return null;
        }
        $vendorId = $collection->getFirstItem()->getVendorId();

        return (int) $vendorId;
    }

    public function verifyEndpoint(){
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            $vendorId = $this->getVendorIdFromToken();

            if ($vendorId == 0) {
                return true;
            }

            $endpointCollection = $this->endpointCollectionFactory->create()
                ->addExpressionFieldToSelect(
                    'stripped_endpoint',
                    "IF(LOCATE(':', endpoint), SUBSTRING_INDEX(endpoint, ':', 1), SUBSTRING_INDEX(endpoint, '{', 1))",
                    ['endpoint' => 'endpoint']
                )
                ->addFieldToFilter('method', $_SERVER['REQUEST_METHOD'])
                ->addFieldToFilter('endpoint_type', $this::ENDPOINT_TYPE_REST_API)
                ->addFieldToFilter('vendor_id', ['in' => [0, $vendorId]])
                ->setOrder('endpoint', 'DESC')
            ;

            foreach ($endpointCollection as $endpoint) {
                if (strpos($uri, $endpoint->getStrippedEndpoint()) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
