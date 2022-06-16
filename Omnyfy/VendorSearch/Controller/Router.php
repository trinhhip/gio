<?php

namespace Omnyfy\VendorSearch\Controller;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var \Omnyfy\VendorSearch\Helper\Data
     */
    protected $_data;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Omnyfy\VendorSearch\Helper\Data $data
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_data = $data;
    }

    /**
     * Validate and Match VendorSearch Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if($request->getFrontName() == "vendorsearch" && !$this->_data->isEnabled()){
            $this->_response->setRedirect('/');
            $request->setDispatched(true);
            return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
        }
    }
}
