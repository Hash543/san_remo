<?php
namespace Mageplaza\Shopbybrand\Controller\Landing;

class View extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_urlRewrite;

	protected $_urlRewriteFactory;

	protected $_urlInterface;

	protected $_requestInterface;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Mageplaza\Shopbybrand\Block\Brand\Landing $landing,
		\Mageplaza\Shopbybrand\Helper\Data $helper,
		\Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
		\Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
		\Magento\Framework\App\Request\Http $urlInterface,
		\Magento\Framework\App\RequestInterface $requestInterface,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->landing = $landing;
		$this->helper = $helper;
		$this->_urlRewriteFactory = $urlRewriteFactory;
		$this->_urlInterface = $urlInterface;
		$this->_urlRewrite = $urlRewrite;
		$this->_requestInterface = $requestInterface;
		return parent::__construct($context);
	}

	public function execute()
	{

		$resultPageFactory =$this->_pageFactory->create();
		$brand = $this->landing->getBrand();
		$pageTitle = $brand->getPageTitle();
		$pageUrl =$brand->getUrlKey();
		$brandRoute = $this->helper->getRoute();
        
        $resultPageFactory->getConfig()->getTitle()->set(__($pageTitle));

        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb($brandRoute,
            [
                'label' => __($brandRoute),
                'title' => __($brandRoute),
                'link' => $this->_url->getUrl($brandRoute)
            ]
        );
        $breadcrumbs->addCrumb($pageUrl,
            [
                'label' => __($pageTitle),
                'title' => __($pageTitle)
            ]
        );

      	/*$fullactionName = str_replace('_', '/',$this->_requestInterface->getFullActionName());
		$param = str_replace('=', '/', http_build_query($this->_urlInterface->getParams(), null, '/')).'/';
		$request_path = $fullactionName.'/'.$param;
		$tareget_path = $brandRoute.'/premium/'.$brand->getUrlKey().'.html';

        $UrlRewriteCollection = $this->_urlRewrite->getCollection()->addFieldToFilter('target_path', $tareget_path);
	    
	    if (!$UrlRewriteCollection->getFirstItem()->getId()) {
	  		$urlRewriteModel = $this->_urlRewriteFactory->create();
			$urlRewriteModel->setStoreId(1);
			$urlRewriteModel->setIsSystem(0);
			$urlRewriteModel->setIdPath(rand(1, 100000));
			$urlRewriteModel->setEntityType('custom');
			$urlRewriteModel->setTargetPath($tareget_path);
			$urlRewriteModel->setRequestPath($request_path);
			$urlRewriteModel->setRedirectType(301);
			$urlRewriteModel->save();
	    }*/

		return $resultPageFactory;
	}
}