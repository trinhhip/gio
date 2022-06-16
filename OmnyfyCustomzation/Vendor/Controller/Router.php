<?php


namespace OmnyfyCustomzation\Vendor\Controller;


use Magento\Framework\App\Action\Forward;

class Router implements \Magento\Framework\App\RouterInterface
{
    const PREFIX_URL = 'shop/';
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
    /**
     * @var \OmnyfyCustomzation\Vendor\Helper\Url
     */
    protected $helpUrl;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \OmnyfyCustomzation\Vendor\Helper\Url $helpUrl
    )
    {
        $this->actionFactory = $actionFactory;
        $this->helpUrl = $helpUrl;
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if (strpos($identifier, self::PREFIX_URL) !== false) {
            $vendorUrl = str_replace('.html', '', str_replace(self::PREFIX_URL, '', $identifier));
            $vendorId = $this->helpUrl->getVendorIdByUrl($vendorUrl);
            if ($vendorId) {
                $request->setModuleName('shop');
                $request->setControllerName('brands');
                $request->setActionName('view');
                $request->setParams([
                    'id' => $vendorId
                ]);
                return $this->actionFactory->create(Forward::class, ['request' => $request]);
            }
        }
        return null;
    }
}