<?php

namespace Alfa9\StoreInfo\Controller\View;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory as StockistsCollectionFactory;
use Alfa9\StoreInfo\Model\Stores;
use Magento\Store\Model\StoreManagerInterface;
use Alfa9\StoreInfo\Block\Stockists;

/**
 * Class Index
 * @package Alfa9\StoreInfo\Controller\View
 */
class Index extends Action
{
    /**
     * @var string
     */
    const META_DESCRIPTION_CONFIG_PATH = 'storeinfo/stockist_content/meta_description';

    /**
     * @var string
     */
    const META_KEYWORDS_CONFIG_PATH = 'storeinfo/stockist_content/meta_keywords';

    /**
     * @var string
     */
    const META_TITLE_CONFIG_PATH = 'storeinfo/stockist_content/meta_title';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /** @var \Magento\Framework\View\Result\PageFactory  */
    public $resultPageFactory;

    /**
     * @var StockistsCollectionFactory
     */
    public $stockistsCollectionFactory;

    /**
     * Configuration
     *
     * @var Stockists
     */
    protected $stockistsConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        StockistsCollectionFactory $stockistsCollectionFactory,
        StoreManagerInterface $storeManager,
        Stockists $stockistsConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->stockistsCollectionFactory = $stockistsCollectionFactory;
        $this->storeManager = $storeManager;
        $this->stockistsConfig = $stockistsConfig;
    }

    /**
     * Load the page defined in view/frontend/layout/stockists_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $link = array_keys($params);

        if(!isset($link[0])) {
            return $this->_redirect(null, ['_direct'=> 'storeinfo']);
        }

        $details = $this->getStoreDetails($link[0]);
        $allStores = $this->getAllStockistStores();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('stockists.stores.individual')->setDetails($details);
        $resultPage->getLayout()->getBlock('stockists.stores.individual')->setAllStores($allStores );

        $resultPage->getConfig()->getTitle()->set(__($this->scopeConfig->getValue(self::META_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE)));
        $resultPage->getConfig()->setDescription(
            $this->scopeConfig->getValue(self::META_DESCRIPTION_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setKeywords(
            $this->scopeConfig->getValue(self::META_KEYWORDS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );

        return $resultPage;

    }

    /**
     * return data from the loaded store details. Only the first store is returned if there are multiple urls
     *
     * @return array
     */
    public function getStoreDetails($url)
    {
        $collection = $this->getIndividualStore($url);
        $stockis = $collection->getFirstItem();
        if($stockis) {
            return $stockis->getData();
        }else {
            return [];
        }
    }

    /**
     * return data from the loaded store details. Only the first store is returned if there are multiple urls
     *
     * @return array
     */
    public function getAllStockistStores()
    {
        $collection = $this->getAllStoresCollection();
        $data = [];
        foreach($collection as $stockist){
            $data[] = $stockist->getData();
        }
        return $data;
    }

    /**
     * return stockists collection filtered by url
     * @param string $url
     * @return \Alfa9\StoreInfo\Model\ResourceModel\Stores\Collection
     */
    public function getIndividualStore($url)
    {
        $collection = $this->stockistsCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', Stores::STATUS_ENABLED)
            ->addFieldToFilter('link', $url)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('name', 'ASC');
        return $collection;
    }

    /**
     * return stockists collection 
     *
     * @return CollectionFactory
     */
    public function getAllStoresCollection()
    {
        $collection = $this->stockistsCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', Stores::STATUS_ENABLED)
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('name', 'ASC');
        return $collection;
    }

}
