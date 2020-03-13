<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_LayeredNavigation
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Weltpixel TEAM
 */

namespace WeltPixel\LayeredNavigation\Plugin\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogSearch\Helper\Data as CatalogSearchHelper;
use WeltPixel\LayeredNavigation\Helper\Data as WpHelper;

/**
 * Class View
 * @package WeltPixel\LayeredNavigation\Plugin\Category
 */
class ResultView
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var CatalogSearchHelper
     */
    protected $_catalogSearchHelper;

    /**
     * @var WpHelper
     */
    protected $_wpHelper;

    /**
     * ResultView constructor.
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param CatalogSearchHelper $catalogSearchHelper
     * @param WpHelper $wpHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        CatalogSearchHelper $catalogSearchHelper,
        WpHelper $wpHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_catalogSearchHelper = $catalogSearchHelper;
        $this->_wpHelper = $wpHelper;
    }

    /**
     * @param \Magento\CatalogSearch\Controller\Result\Index $subject
     * @param \Closure $method
     * @return $this|array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        \Magento\CatalogSearch\Controller\Result\Index $subject,
        \Closure $method
    ){
        if($subject->getRequest()->getParam('ajax') == 1 && $this->_wpHelper->isEnabled()){
            $this->_wpHelper->updateSliderBodyClass();
            $requestUri = $subject->getRequest()->getRequestUri();
            $requestUri = preg_replace('/(\?|&)ajax=1/', '', $requestUri);
            $subject->getRequest()->setRequestUri($requestUri);
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
            /* @var $query \Magento\Search\Model\Query */
            $query = $this->_queryFactory->get();

            $query->setStoreId($this->_storeManager->getStore()->getId());

            $resultJson = $this->_resultJsonFactory->create();

            if ($query->getQueryText() != '') {

                if ($this->_catalogSearchHelper->isMinQueryLength()) {
                    $query->setId(0)->setIsActive(1)->setIsProcessed(1);
                } else {
                    $query->saveIncrementalPopularity();

                    if ($query->getRedirect()) {
                        $data = [
                            'success' => true,
                            'redirect_url' => $query->getRedirect()
                        ];
                        return $resultJson->setData($data);
                    }
                }

                $this->_catalogSearchHelper->checkNotes();
                $pageFactory = $this->_resultPageFactory->create();
                $resultsBlockHtml = $pageFactory->getLayout()->getBlock('search.result')->toHtml();
                $leftNavBlockHtml = $pageFactory->getLayout()->getBlock('catalogsearch.leftnav')->toHtml();

                $dataLayerContent = '';
                $dataLayerBlock = $pageFactory->getLayout()->getBlock('head.additional');
                if ($dataLayerBlock) {
                    $dLBlockHtml = $dataLayerBlock->toHtml();

                    preg_match('/var dlObjects = (.*?);/', $dLBlockHtml, $matches);
                    if (count($matches) == 2) {
                        $dataLayerContent = $matches[1];
                    }
                }

                return $this->_resultJsonFactory->create()->setData(
                    [
                        'success' => true,
                        'html' => [
                            'products_list' => $resultsBlockHtml,
                            'filters' => $leftNavBlockHtml,
                            'dataLayer' => $dataLayerContent
                        ]
                    ]
                );
                return $data;
            } else {
                $data = [
                    'success' => true,
                    'redirect_url' => $this->_redirect->getRedirectUrl()
                ];
                return $resultJson->setData($data);
            }
        } else {
            return $method();
        }

    }

}